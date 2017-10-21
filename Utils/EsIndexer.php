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