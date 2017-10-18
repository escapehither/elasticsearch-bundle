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

class EsClient
{
    protected $client;
    /**
     * EsClient constructor.
     */
    public function __construct($hosts = NULL)
    {
        $this->client = self::ClientBuild();
    }

    /**
     * @param null $hosts
     * @return Client
     */
    protected static function ClientBuild($hosts = NULL) {
        // If there ist host settings.
        if (!empty($hosts)) {
            $client = ClientBuilder::create()->setHosts($hosts)->build();
        }
        else {
            // Use the default settings.
            $client = ClientBuilder::create()->build();
        }
        return $client;

    }

    /**
     * @return array
     */
    public function getHealth(){
        return $this->client->cluster()->health();
    }
    public function getSettings(){
        return $this->client->indices()->getSettings();
    }
    public function create($params){
        return $this->indices()->create($params);
    }
}