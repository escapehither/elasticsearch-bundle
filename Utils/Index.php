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
     * @param $client
     */
    public function __construct($name, EsClient $client)
    {
        $this->name = $name;
        $this->client = $client;
    }


    /**
     *  Create a new index with mapping if set
     * @param $name
     * @param array $mapping
     * @return array
     */
    public function createIndex($name, $mapping = []) {
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
                    $response = $this->client->create($params);
                    return $response;
                }


            }

        } catch (\Exception $e) {
        }
        return $response;

    }

}