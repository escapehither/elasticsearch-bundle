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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use EscapeHither\SearchManagerBundle\Utils\Document;

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
     * @return array|object|\Symfony\Component\Serializer\Normalizer\scalar
     */
    public function CreateDocumentField() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        if (!empty($this->configuration['tags'])) {
            foreach ($this->configuration['tags'] as $keyTag => $exclude) {
                $collectionToTagsCallback = $this->getTagGenerator();
                $normalizer->setCallbacks(array($keyTag => $collectionToTagsCallback));
            }
        }
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getName();
        });
        $normalizers = array($normalizer);
        $serializer = new Serializer($normalizers, $encoders);
        $document = $serializer->normalize($this->object);
        return $document;
    }

    /**
     * @return \EscapeHither\SearchManagerBundle\Utils\Document
     */
    public function createDocument(){
        return new  Document($this->configuration['type'],$this->CreateDocumentField());
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