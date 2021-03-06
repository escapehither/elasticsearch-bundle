<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;

/**
 * Class EsClient
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class EsClient
{
    protected $client;
    protected $settings;

    /**
     * Es client constructor.
     *
     * @param null $hosts
     */
    public function __construct($hosts = null)
    {
        $this->client = self::clientBuild($hosts);
        $this->settings = $this->getSettings();
    }

    /**
     * Get the cluster health.
     *
     * @return array
     */
    public function getHealth()
    {
        try {
            return $this->client->cluster()->health();
        } catch (\Exception $e) {
            error_log($e->getMessage(), 0);
        }

        return false;
    }

    /**
     * Get the settings.
     *
     * @param array $config
     *
     * @return array
     */
    public function getSettings($config = [])
    {
        if ($this->getHealth()) {
            return $this->client->indices()->getSettings($config);
        }

        return [];
    }

    /**
     * Create a new elastic search index.
     *
     * @param array $params The params.
     *
     * @return mixed
     */
    public function createIndex($params)
    {
        return $this->client->indices()->create($params);
    }

    /**
     * Delete an Index.
     *
     * @param Index $index The index to delete.
     *
     * @return array
     */
    public function deleteIndex(Index $index)
    {
        return $this->client->indices()->delete(['index' => $index->getName()]);
    }

    /**
     * Check if the index exist.
     *
     * @param Index $index The index.
     *
     * @return bool
     */
    public function ifIndexExist(Index $index)
    {
        if (isset($this->settings[$index->getName()])) {
            return true;
        }

        return false;
    }

    /**
     * Put Document mapping.
     *
     * @param Document $document The documents.
     * @param Index    $index    The index.
     */
    public function putDocumentMapping(Document $document, Index $index)
    {
        $paramsMapping['index'] = $index->getName();
        $paramsMapping['type'] = $document->getType();
        $paramsMapping['body'] = $document->getMapping();
        $this->client->indices()->putMapping($paramsMapping);
    }

    /**
     * @param Index $index
     *
     * @return array
     */
    public function getIndexMappings(Index $index)
    {
        $params = [
          'index' => $index->getName(),
        ];

        return $this->client->indices()->getMapping($params)[$index->getName(
        )]['mappings'];
    }

    /**
     * Index a document.
     *
     * @param Document $document The document.
     * @param Index    $index    The index.
     *
     * @return array
     */
    public function index(Document $document, Index $index)
    {
        $params['index'] = $index->getName();
        $params['type'] = $document->getType();

        if (null !== $document->getId()) {
            $params['id'] = $document->getId();
        }

        $params['body'] = $document->getField();

        return $this->client->index($params);
    }

    /**
     * Delete a document.
     *
     * @param array $params The params.
     *
     * @return array
     */
    public function delete($params)
    {
        return $this->client->delete($params);
    }

    /**
     * Get a document
     *
     * @param array $params The de params.
     *
     * @return array
     */
    public function get($params)
    {
        return $this->client->get($params);
    }

    /**
     * Search a resource.
     *
     * @param array $params The params.
     *
     * @return array
     */
    public function search($params)
    {
        return $this->client->search($params);
    }

     /**
     * Build the client.
     *
     * @param null $hosts
     *
     * @return Client
     */
    protected static function clientBuild($hosts = null)
    {
        if (!empty($hosts)) {
            $client = ClientBuilder::create()->setHosts(['host' => $hosts])->build();
        } else {
            // Use the default settings.
            $client = ClientBuilder::create()->build();
        }

        return $client;
    }
}
