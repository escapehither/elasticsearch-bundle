<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp;

/**
 * Class Index
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class Index implements IndexInterface
{
    protected $name;
    protected $type;
    protected $client;

    /**
     * Index constructor.
     *
     * @param string   $name   The index name
     * @param EsClient $client A small layer add to ElasticSearch Client
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
     * Get the index settings
     */
    public function getSettings()
    {
        try {
            if ($this->client->getHealth()) {
                $this->client->getSettings(['index' => $this->name]);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage().''.$e->getFile().''.$e->getLine(), 0);
        }
    }

    /**
     * Create a new index with mapping if set
     *
     * @param array $mapping
     *
     * @return array|mixed
     */
    public function create($mapping = [])
    {
        $response = [];
        try {
            // Add exception control.
            if ($this->client->getHealth()) {
                // Get settings for one index.
                // Check if index.
                if (!in_array($this->name, $this->client->getSettings())) {
                    // Create the index.
                    return $this->client->createIndex(
                        $this->getDefaultParameters($mapping)
                    );
                }
            }
        } catch (\Exception $e) {
            //TODO HANDLE ERROR LOG
            error_log($e->getMessage().''.$e->getFile().''.$e->getLine(), 0);
        }

        return $response;
    }

    /**
     * Delete index
     */
    public function delete()
    {
        try {
            if ($this->client->getHealth()) {
                $this->client->deleteIndex($this);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage().''.$e->getFile().''.$e->getLine(), 0);
        }
    }

    /**
     * Index a document.
     *
     * @param \EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Document $document
     */
    public function indexDocument(Document $document)
    {
        try {
            if ($this->client->getHealth()) {
                // Check if index exist before proceeding.
                if ($this->client->ifIndexExist($this)) {
                    if (array_key_exists(
                        $document->getType(),
                        $this->getMapping()
                    )) {
                        $this->client->index($document, $this);
                    } else {
                        if (!empty($document->getMapping())) {
                            $this->client->putDocumentMapping($document, $this);
                        }
                        $this->client->index($document, $this);
                    }
                } else {
                    $response = $this->create($document->getMapping());
                    if (isset($response['acknowledged'])) {
                        $this->client->index($document, $this);
                    } else {
                        //TODO WHAT IF NOTHING HAPPEN.
                    }
                }
            }
        } catch (\Exception $e) {
            //TODO HANDLE ERROR LOG
            error_log($e->getMessage().''.$e->getFile().''.$e->getLine(), 0);
        }
    }

    /**
     * Delete a document.
     *
     * @param string $type The document type.
     * @param mixed  $id   The document id.
     */
    public function deleteDocument($type, $id)
    {

        $params['index'] = $this->name;
        $params['type'] = $type;
        $params['id'] = $id;
        try {
            if ($this->client->getHealth()) {
                if ($this->client->ifIndexExist($this)) {
                    $this->client->delete($params);
                }
            }
        } catch (\Exception $e) {
            error_log($e->getMessage().''.$e->getFile().''.$e->getLine(), 0);
        }
    }

    /**
     * Get a ssingle document.
     *
     * @param string $type The document type.
     * @param mixed  $id   The document id.
     *
     * @return array
     */
    public function getDocument($type, $id)
    {
        $params['index'] = $this->name;
        $params['type'] = $type;
        $params['id'] = $id;
        $response = [];

        try {
            if ($this->client->getHealth()) {
                if ($this->client->ifIndexExist($this)) {
                    $response = $this->client->get($params);
                }
            }
        } catch (\Exception $e) {
            error_log($e->getMessage().''.$e->getFile().''.$e->getLine(), 0);
        }

        return $response;
    }

    /**
     * Get Index current mapping
     *
     * @return array
     */
    public function getMapping()
    {
        return $this->client->getIndexMappings($this);
    }

    /**
     *Get Mapping default parameter.
     *
     * @param array $mapping The resource mapping.
     *
     * @return array
     */
    public function getDefaultParameters($mapping = [])
    {
        $params = ['index' => $this->name];
        $params['body'] = [
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'folding_analyzer' => [
                            'tokenizer' => "standard",
                            'filter' => ["standard", "asciifolding", "lowercase", "word_delimiter"],
                        ],
                    ],
                ],
            ],
        ];

        if (!empty($mapping) && is_array($mapping)) {
            $params['body']['mappings'] = $mapping;
        }

        return $params;
    }

    /**
     * Search over an index.
     *
     * @param SearchReQuestInterface $searchRequest
     *
     * @return array
     */
    public function search(SearchReQuestInterface $searchRequest)
    {
        $params = $searchRequest->generateRequest();

        if (!isset($params['index'])) {
            $params['index'] = $this->name;
        }

        $results = [];

        try {
            $results = $this->client->search($params);
        } catch (\Exception $e) {
            error_log($e->getMessage().''.$e->getFile().''.$e->getLine(), 0);
        }

        return $results;
    }
}
