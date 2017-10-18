<?php
/**
 * This file is part of the search-manager-bundle package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 18/10/17
 * Time: 22:32
 */

namespace EscapeHither\SearchManagerBundle\Utils;


/**
 * Class Index
 * @package EscapeHither\SearchManagerBundle\Utils
 */
class Index
{
    protected $name;
    protected $type;
    protected $client;

    /**
     * Index constructor.
     * @param $name
     *  The index name
     * @param $client
     *  A small layer add to ElasticSearch Client
     */
    public function __construct($name, EsClient $client)
    {
        $this->name = $name;
        $this->client = $client;
    }
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     *  Create a new index with mapping if set
     * @param $name
     * @param array $mapping
     * @return array
     */
    public function create($name, $mapping = []) {
        $response = [];
        try {
            // Add exception control.
            if ($this->client->getHealth()) {
                $params = ['index' => $this->name];
                $params['body'] = [
                    'settings' => [
                        'analysis' => [
                            'analyzer' => [
                                'folding_analyzer' => [
                                    'tokenizer' => "standard",
                                    'filter' => ["standard", "asciifolding", "lowercase"]
                                ]
                            ]
                        ]
                    ]
                ];
                if (!empty($mapping['mappings'] && is_array($mapping['mappings']))) {
                    $params['body']['mappings'] = $mapping['mappings'];
                }

                // Get settings for one index.
                // Check if index.
                if (!in_array($params['index'], $this->client->getSettings())) {
                    // Create the index.
                    $response = $this->client->createIndex($params);
                    return $response;
                }


            }

        } catch (\Exception $e) {
        }
        return $response;

    }
    /**
     * Index a document.
     * @param string $indexName
     *   The index name.
     * @param string $type
     *   The document type.
     * @param int    $id
     *   The document id.
     * @param array  $fields
     *   The documents fields.
     * @return array
     *   The status.
     */
    /*public function indexDocument($type, $id, $fields) {


        $params['index'] = $this->getName();
        $params['type'] = $type;
        if ($id != NULL) {
            $params['id'] = $id;
        }
        $params['body'] = $fields;
        try {

            if ($this->client->getHealth()) {
                // Get settings for one index.
                $response = $this->client->getSettings();
                // Check if index exist before proceeding.
                if ($this->client->ifIndexExist($this)) {
                    if (array_key_exists($type, self::getMappings($indexName)[$indexName]['mappings'])) {
                        $this->client->index($params);
                    }
                    else {
                        if(!empty(self::getConfigMapping($type))){
                            $paramsMapping['index'] = $params['index'];
                            $paramsMapping['type'] = $params['type'];
                            $paramsMapping['body'] = self::getConfigMapping($type)['mappings'];
                            $client->indices()->putMapping($paramsMapping);
                        }
                        $client->index($params);
                    }
                }
                else {
                    $response = self::createIndex($this->getName(), self::getConfigMapping($type));
                    if ($response['acknowledged']) {
                        $client->index($params);

                    }
                }

            }

        } catch (\Exception $e) {
        }


    }*/

}