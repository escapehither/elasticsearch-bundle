<?php
/**
 * This file is part of the search-manager-bundle package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 18/10/17
 * Time: 23:30
 */

namespace EscapeHither\SearchManagerBundle\Utils;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;

/**
 * This Class add a small layer to Elastic Search client.
 * Class EsClient
 * @package EscapeHither\SearchManagerBundle\Utils
 */
class EsClient
{
    protected $client;
    protected $settings;

    /**
     * @param null $hosts
     */
    public function __construct($hosts = NULL)
    {
        $this->client = self::ClientBuild();
        $this->settings = $this->getSettings();
    }

    /**
     * @param null $hosts
     * @return Client
     */
    protected static function ClientBuild($hosts = NULL)
    {
        // If there ist host settings.
        if (!empty($hosts)) {
            $client = ClientBuilder::create()->setHosts($hosts)->build();
        } else {
            // Use the default settings.
            $client = ClientBuilder::create()->build();
        }
        return $client;

    }

    /**
     * @return array
     */
    public function getHealth()
    {
        return $this->client->cluster()->health();
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->client->indices()->getSettings();
    }

    /**
     *  Create a new elastic search index.
     * @param $params
     * @return mixed
     */
    public function createIndex($params)
    {
        return $this->client->indices()->create($params);
    }

    /**
     * Delete an Index.
     * @param Index $index
     *  The index to delete.
     * @return array
     */
    public function deleteIndex(Index $index)
    {
        return $this->client->indices()->delete($index->getName());
    }

    /**
     * Check if the index exist
     * @param Index $index
     * @return bool
     */
    public function ifIndexExist(Index $index)
    {
        if (isset($this->settings[$index->getName()])) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    /**
     * @param Document $document
     * @param Index $index
     */
    public function putDocumentMapping(Document$document, Index $index)
    {
        $paramsMapping['index'] = $index->getName();
        $paramsMapping['type'] = $document->getType();
        $paramsMapping['body'] = $document->getMapping();
        $this->client->indices()->putMapping($paramsMapping);
    }
    /**
     * @param $index
     * @return array
     */
    public function getIndexMappings(Index $index) {
        $params = [
            'index' => $index->getName(),
        ];
        return $this->client->indices()->getMapping($params)[$index->getName()]['mappings'];
    }

    /**
     * @param Document $document
     * @param Index $index
     */
    public function index(Document$document, Index $index){
        $params['index'] = $index->getName();
        $params['type'] = $document->getType();
        if ($document->getId() != NULL) {
            $params['id'] = $document->getType();
        }
        $params['body'] = $document->getField();
        $this->client->index($params);
    }
}