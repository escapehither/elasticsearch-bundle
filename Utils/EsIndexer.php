<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 08/07/17
 * Time: 16:35
 */

namespace EscapeHither\SearchManagerBundle\Utils;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;
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
     * Create new index.
     * @param string $name
     *   The index name.
     */
    public static function createIndex($name) {
        $client = self::ClientBuild();
        try {
            // Add exception control.
            if ($client->cluster()->health()) {
                $params = ['index' => $name];
                $response = $client->indices()->getSettings();
                if (!in_array($params['index'], $response)) {
                    // Create the index.
                    $client->indices()->create($params);

                }

            }

        } catch (\Exception $e) {
            //drupal_set_message($e->getMessage(), 'error');
        }

    }

    public static function resetIndex($name) {
        $client = self::ClientBuild();
        $params['index'] = $name;
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
     * @param string $index_name
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
    public static function IndexDocument($index_name, $type, $id, $fields) {

        $client = self::ClientBuild();
        $params['index'] = $index_name;
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
                if (isset($response[$index_name])) {
                     $client->index($params);
                }
                else {
                    // Create the index.
                    $response = self::createIndex($index_name);
                    if ($response['acknowledged']) {
                        $client->index($params);
                    }
                }

            }

        } catch (\Exception $e) {

        }


    }

    /**
     * @param $index_name
     * @param $type
     * @param $id
     * @return mixed
     */

    public static function GetDocument($index_name, $type, $id) {

        $client = self::ClientBuild();
        $params['index'] = $index_name;
        $params['type'] = $type;
        $params['id'] = $id;
        $response = [];
        try {

            if ($client->cluster()->health()) {
                // Get settings for one index.
                $response = $client->indices()->getSettings();

                // Check if index exist before proceeding.
                if (isset($response[$index_name])) {
                    $response = $client->get($params);

                }

            }


        } catch (\Exception $e) {

        }
        return $response;

    }

    /**
     * @param $index_name
     * @param $type
     * @param $id
     */
    public static function DeleteDocument($index_name, $type, $id) {

        $client = self::ClientBuild();
        $params['index'] = $index_name;
        $params['type'] = $type;
        $params['id'] = $id;
        try {
            if ($client->cluster()->health()) {
                // Get settings for one index.
                $response = $client->indices()->getSettings();
                // Check if index exist before proceeding.
                if (!empty($response[$index_name])) {
                   $client->delete($params);
                }

            }

        } catch (\Exception $e) {
        }


    }

}