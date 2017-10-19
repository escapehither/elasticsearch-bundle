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
     * Create a new index with mapping if set
     * @param array $mapping
     * @return array|mixed
     */
    public function create($mapping = [])
    {
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
     * @param \EscapeHither\SearchManagerBundle\Utils\Document $document
     */
    public function indexDocument(Document $document)
    {


        try {

            if ($this->client->getHealth()) {
                // Check if index exist before proceeding.
                if ($this->client->ifIndexExist($this)) {
                    if (array_key_exists($document->getType(), $this->getMapping())) {
                        $this->client->index($document,$this);
                    } else {
                        if (!empty($document->getMapping())) {
                            $this->client->putDocumentMapping($document,$this);
                        }
                        $this->client->index($document,$this);
                    }
                } else {
                    $response = $this->create($document->getMapping());
                    if ($response['acknowledged']) {
                        $this->client->index($document,$this);

                    }
                }

            }

        } catch (\Exception $e) {
        }
    }

    /**
     * @return array
     */
    public function getMapping(){
        return $this->client->getIndexMappings($this);
    }


}