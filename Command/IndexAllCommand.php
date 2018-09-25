<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 10/07/17
 * Time: 20:32
 */

namespace EscapeHither\SearchManagerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\Question;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Index;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\EsClient;
use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;

/**
 * Index all command class.
 */
class IndexAllCommand extends ContainerAwareCommand
{
    protected $kernel;
    protected $doctrine;
    protected $indexes;
    protected $configuration;
    protected $host;

    const INDEX_NAME = 'index_name';
    const FIELD_NAME = 'fieldName';
    const ENTITY_CONFIG = 'entity';

     /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('escapehither:esm:index:all')
            ->setDescription('Populate an elastic search index')
            ->setHelp('This command allows you to index all ...') ;
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $this->kernel = $this->getApplication()->getKernel();
        $this->host = $this->kernel->getContainer()->getParameter('escape_hither.search_manager.host');
        $this->doctrine = $this->kernel->getContainer()->get('doctrine');

        $output->writeln([
            'Index all content in an Elasticsearch index',
            '============',
            '',
        ]);
        $helper = $this->getHelper('question');
        $indexName = $this->askInput($input, $output, $helper);
        $em = $this->doctrine->getEntityManager();
        $data = $em->getRepository($this->configuration['entity'])->findAll();

        foreach ($data as $key => $object) {
            $class = get_class($object);

            if ($this->indexHasParameter($class)) {
                $this->indexDocument($class, $object);
            }
        }

        $output->writeln('The content of index : '.$indexName.' successfully indexed');
    }

    /**
     * Ask input.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param mixed                                             $helper
     * @param null                                              $message
     *
     * @return mixed
     */
    protected function askInput(InputInterface $input, OutputInterface $output, $helper, $message = null)
    {
        $question = new Question('Please the index name: ');
        $this->indexes = $this->kernel->getContainer()->getParameter('escape_hither.search_manager.indexes');

        $question->setValidator(function ($value) {
            $key = array_search($value, array_column($this->indexes, 'index_name'));

            if (false === $key) {
                throw new \Exception(sprintf('The index %s has not been found in your configuration', $value));
            }

            $keys = array_keys($this->indexes);
            $this->configuration = $this->indexes[$keys[$key]];

            return $value;
        });

        $question->setMaxAttempts(3);

        return $helper->ask($input, $output, $question);
    }
    /**
     * Index a document.
     *
     * @param string $class
     * @param mixed  $object
     */
    protected function indexDocument($class, $object)
    {
        $parameter = $this->indexes[$class];
        $documentHandler = new DocumentHandler($object, $parameter);
        $document = $documentHandler->CreateDocument();

        $fieldMappings = $this->getEntityMetadataFieldMappings($class);

        foreach ($fieldMappings as $key => $value) {
            if ('string' === $value['type']) {
                $mapping[$document->getType()]['properties'][$key] = $this->getDefaultStringAnalyzer();
            }
        }

        $document->setMapping($mapping);
        $document->setId($object->getId());
        $this->getIndex($parameter[self::INDEX_NAME])->indexDocument($document);
    }

    /**
     * Get the index.
     *
     * @param string $indexName
     *
     * @return Index
     */
    protected function getIndex($indexName)
    {
        return $index = new Index($indexName, new EsClient($this->host));
    }

    /**
     * Get the Entity metadata field mappings.
     *
     * @param mixed $entity
     *
     * @return array
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function getEntityMetadataFieldMappings($entity)
    {
        // TODO ADD CASHING
        $factory = new DisconnectedMetadataFactory($this->doctrine);
        $metadataClass = $factory->getClassMetadata($entity)->getMetadata()[0];
        $baseMapping = $metadataClass->fieldMappings;

        foreach ($metadataClass->associationMappings as $fieldAssociation => $association) {
            $metadataAssociation = $factory->getClassMetadata($association['targetEntity'])->getMetadata()[0];
            $mappingAssociation = $metadataAssociation->fieldMappings;

            foreach ($mappingAssociation as $keyMapping => $mapping) {
                $name = $fieldAssociation.'.'.$mapping[self::FIELD_NAME];
                $baseMapping[$name] = $mapping ;
            }
        }

        return $baseMapping;
    }

    /**
     * @return array
     */
    protected function getDefaultStringAnalyzer()
    {
        return $default = [
            'type' => 'string',
            'analyzer' => 'standard',
            'fields' => [
            'asciifolding' => [
                'type' => 'string',
                'analyzer' => 'folding_analyzer',
            ],
            'exact_value' => [
                'type' => 'string',
                'index' => 'not_analyzed',
            ],
            ],
        ];
    }

    /**
     * Get parameter Class.
     *
     * @param string $class The resource class.
     *
     * @return string
     */
    protected function getParameterClass($class)
    {
        return $class;
    }

    /**
     * Index has parameter.
     *
     * @param string $class The resource class.
     *
     * @return bool
     */
    protected function indexHasParameter($class)
    {
        return array_key_exists($class, $this->indexes);
    }
}
