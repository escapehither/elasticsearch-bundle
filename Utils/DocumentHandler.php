<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 09/07/17
 * Time: 16:23
 */

namespace EscapeHither\SearchManagerBundle\Utils;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Document;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
// For annotations
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

/**
 *  Document Creator.
 * Class DocumentHandler
 * @package EscapeHither\SearchManagerBundle\Utils
 */
class DocumentHandler {
    private $configuration;
    private $object;

    /**
     * @param $object
     * @param $config
     */
    public function __construct($object,$config) {
    $this->object = $object;
    $this->configuration = $config;
    }

    /**
     * @return mixed
     */
    public function getConfiguration() {
        return $this->configuration;
    }

    /**
     * @param mixed $configuration
     */
    public function setConfiguration($configuration) {
        $this->configuration = $configuration;
    }

    /**
     * @return \Closure
     */
    public function getTagGenerator() {
        return function (PersistentCollection $persistentCollection) {
            $normalizer = new ObjectNormalizer();
            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });
            $normalizers = array($normalizer);
            $serializer = new Serializer($normalizers);
            $fieldName = $persistentCollection->getMapping()['fieldName'];
            $tab = $persistentCollection->toArray();
            $tag = [];
            foreach ($tab as $item) {
                foreach ($serializer->normalize($item) as $name => $value) {
                    $tagInclusion = $this->getTagInclusion($fieldName);
                    if(!empty($tagInclusion) ){
                        if(in_array($name,$tagInclusion)){
                            $tag[$name][] = $value;
                        }

                    }else{
                        $tag[$name][] = $value;
                    }
                }
            }
            return $tag;
        };
    }

    /**
     * @return array
     */

    public function CreateDocumentFields() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        //dump($normalizer);
        if (!empty($this->configuration['tags'])) {
            foreach ($this->configuration['tags'] as $keyTag => $exclude) {
                $collectionToTagsCallback = $this->getTagGenerator();
                $normalizer->setCallbacks(array($keyTag => $collectionToTagsCallback));
            }
        }
        $normalizer->setCircularReferenceHandler(function ($object) {
            //TODO interface
            return $object->getName();
        });
        $dataUriNormalizer = new DataUriNormalizer();
        $normalizers = array($normalizer,$dataUriNormalizer);
        $serializer = new Serializer($normalizers, $encoders);
        $documentFields = $serializer->normalize($this->object, NULL, array('groups' => array('index')));
        //dump($documentFields);
        //die();
        return $documentFields;
    }

    /**
     * @return \EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Document
     */
    public function createDocument(){

        return new  Document($this->configuration['type'],$this->CreateDocumentFields());
    }

    /**
     * @param $fieldName
     * @return array
     */
    private function getTagInclusion($fieldName){
        $exclusion = [];
        $configurationTag = $this->getTagsConfig();
        if(!empty($configurationTag) && !empty($configurationTag[$fieldName]['include'])){
            $exclusion = $configurationTag[$fieldName]['include'];
        }
        return $exclusion;

    }

    /**
     * @return array
     */
    private function getTagsConfig(){
        if(!empty($this->configuration['tags'])){
            return $this->configuration['tags'];
        }
        return [];

    }

}