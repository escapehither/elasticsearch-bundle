<?php
/**
 * This file is part of the Search Manager Bundle package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 08/07/17
 * Time: 16:35
 */

namespace EscapeHither\SearchManagerBundle\Utils;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;

/**
 * Class EsIndexer
 * @package EscapeHither\SearchManagerBundle\Utils
 */
class EsIndexer {
    /**
     * @param null $hosts
     * @return Client
     */
    public static function ClientBuild($hosts = NULL) {
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
     *  Create a new index with mapping if set
     * @param $name
     * @param array $mapping
     * @return array
     */
    public static function createIndex($name, $mapping = []) {
        $client = self::ClientBuild();
        $response = [];
        try {
            // Add exception control.
            if ($client->cluster()->health()) {
                $params = ['index' => $name];
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
                $response = $client->indices()->getSettings();
                if (!in_array($params['index'], $response)) {
                    // Create the index.
                    $response = $client->indices()->create($params);
                    return $response;
                }


            }

        } catch (\Exception $e) {
        }
        return $response;

    }

    /**
     * @param string $name
     * @param array $mapping
     */
    public static function resetIndex(string $name, $mapping = []) {
        $client = self::ClientBuild();

        $params['index'] = $name;

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
        if (!empty($mapping)) {
            $params['body']['mappings'] = $mapping;
        }

        try {

            if ($client->cluster()->health()) {

                // Get settings for one index.
                // Check if index.
                $response = $client->indices()->getSettings();

                if (array_key_exists($params['index'], $response)) {
                    $client->indices()->delete($params);
                    $client->indices()->create($params);
                }
                else {
                    // Create the index.
                    $client->indices()->create($params);

                }

            }
        } catch (\Exception $e) {
            //drupal_set_message($e->getMessage(), 'error');
        }

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
    public static function indexDocument($indexName, $type, $id, $fields) {

        $client = self::ClientBuild();
        $params['index'] = $indexName;
        $params['type'] = $type;
        if ($id != NULL) {
            $params['id'] = $id;
        }

        $params['body'] = $fields;
        try {

            if ($client->cluster()->health()) {
                // Get settings for one index.
                $response = $client->indices()->getSettings();
                // Check if index exist before proceeding.
                if (isset($response[$indexName])) {
                    if (array_key_exists($type, self::getMappings($indexName)[$indexName]['mappings'])) {
                        $client->index($params);
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
                    $response = self::createIndex($indexName, self::getConfigMapping($type));
                    if ($response['acknowledged']) {
                        $client->index($params);

                    }
                }

            }

        } catch (\Exception $e) {
        }


    }

    /**
     * @param $indexName
     * @param $type
     * @param $id
     * @return mixed
     */

    public static function getDocument($indexName, $type, $id) {

        $client = self::ClientBuild();
        $params['index'] = $indexName;
        $params['type'] = $type;
        $params['id'] = $id;
        $response = [];
        try {

            if ($client->cluster()->health()) {
                // Get settings for one index.
                $response = $client->indices()->getSettings();

                // Check if index exist before proceeding.
                if (isset($response[$indexName])) {
                    $response = $client->get($params);

                }

            }


        } catch (\Exception $e) {

        }
        return $response;

    }

    /**
     * @param $index
     * @return array
     */
    public static function getMappings($index) {
        $client = self::ClientBuild();
        $params = [
          'index' => $index,
        ];
        return $client->indices()->getMapping($params);
    }

    /**
     *  This method handle search result.
     * @param $index
     * @param $type
     * @param null $request
     * @return array
     */
    public static function elasticSearchHandler($index, $type, $request = NULL) {

        // Build the elastic search client.
        $client = self::ClientBuild();

        $text = '*';

        if (isset($request['string'])) {

            if (!is_array($request['string'])) {
                $text = $request['string'] . '*';
            }
            else {
                if (count($request['string']) > 1) {
                    $text = implode("* ", $request['string']);

                }
                else {
                    $text = implode("* ", $request['string']) . '*';


                }


            }

        }

        $parameter = [];
        $parameter['index'] = $index;
        $parameter['type'] = $type;
        $parameter['size'] = 5000;
        // Add sort asc.
        if (isset($request['sort'])) {
            $parameter['body']['sort'] = $request['sort'];
        }


        if (!isset($request['filter'])) {
            // Query run against multiple fields if set.
            if (!empty($request['fields'])) {
                $parameter['body']['query']['query_string']['fields'] = $request['fields'];

            }
        }
        else {
            // Query run against multiple fields if set.
            if (!empty($request['fields'])) {
                $parameter['body']['query']['filtered']['query']['query_string']['fields'] = $request['fields'];

            }
            $parameter['body']['query']['filtered']['filter']['bool']['must'][]['terms'] = $request['filter'];

        }
        if (!empty($request['match'])) {
            // match request example:
            $parameter['body']['query']['filtered']['query']['bool']['must'][] = $request['match'];
        }
        if (!empty($request['match_phrase'])) {
            $parameter['body']['query']['match_phrase'] = $request['match_phrase'];

        }
        if (!empty($request['term'])) {
            // match with the exact value:

            $parameter['body']['query']['filtered']['query']['bool']['must'][] = $request['term'];
        }
        if (!empty($request['exist'])) {
            //Check for an empty field
            $parameter['body']['query']['filtered']['query']['bool']['filter'] = $request['exist'];
        }

        $results = [];
        try {

            $results = $client->search($parameter);
        } catch (\Exception $e) {
            //drupal_set_message($e->getMessage(), 'error');
        }

        return $results;

    }

    /**
     * @param $indexName
     * @param $type
     * @param $id
     */
    public static function deleteDocument($indexName, $type, $id) {

        $client = self::ClientBuild();
        $params['index'] = $indexName;
        $params['type'] = $type;
        $params['id'] = $id;
        try {
            if ($client->cluster()->health()) {
                // Get settings for one index.
                $response = $client->indices()->getSettings();
                // Check if index exist before proceeding.
                if (!empty($response[$indexName])) {
                   $client->delete($params);
                }

            }

        } catch (\Exception $e) {
        }


    }

    /**
     * @param $type
     * @return array
     */
    protected static function getConfigMapping($type) {
        $mapping = [];
        /*try {
            //$mappings_file = DRUPAL_ROOT . "/" . drupal_get_path('module', 'musnew_indexation') . '/mappings.yml';
            $mapping_list = Yaml::parse(file_get_contents($mappings_file));
            if (isset($mapping_list[$type])) {
                $mapping = $mapping_list[$type];

            }
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
        }*/

        return $mapping;

    }

}