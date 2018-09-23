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

/**
 * Index delete Command.
 */
class IndexDeleteCommand extends ContainerAwareCommand
{
    protected $kernel;
    protected $indexes;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('escapehither:esm:index:delete')
            ->setDescription('Delete an Elastic search index')
            ->setHelp('This command allows you to delete an index ...')
        ;
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
        $host = $this->kernel->getContainer()->getParameter('escape_hither.search_manager.host');

        $output->writeln([
            'Delete an Elasticsearch index',
            '============',
            '',
        ]);
        $helper = $this->getHelper('question');
        $indexName = $this->askInput($input, $output, $helper);
        $index = new Index($indexName, new EsClient($host));

        try {
            $index->delete();
            $output->writeln('The Index : '.$indexName.' successfully delete');
        } catch (\Exception $e) {
            $answer = json_decode($e->getMessage());
            $output->writeln('ERROR: '.$answer->error->reason.' '.$answer->error->index);
        }
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

            if (false === array_search($value, array_column($this->indexes, 'index_name'))) {
                throw new \Exception(sprintf('The index %s has not been found in your configuration', $value));
            }

            return $value;
        });

        $question->setMaxAttempts(3);

        return $helper->ask($input, $output, $question);
    }
}
