<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 28/01/17
 * Time: 19:35
 */

namespace EscapeHither\SearchManagerBundle\Services;

use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\SearchRequest;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Index;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\EsClient;

/**
 * The facet provider.
 */
class EsFacetProvider
{

    private $indexConfig;
    private $facets = [];
    private $host;

    /**
     * Facets Provider Constructor.
     *
     * @param [type] $indexConfig
     * @param [type] $host
     */
    public function __construct($indexConfig, $host)
    {
        $this->indexConfig = $indexConfig;
        $this->host = $host;
    }

    /**
     * @param array $results The Search results
     *
     * @return mixed
     */
    public function getFacets($results)
    {
        empty($this->indexConfig['facets']['tags_relation']) ? :$this->addFacetTagRelation();
        empty($this->indexConfig['facets']['dates']) ? :$this->addFacetsDate();
        empty($this->indexConfig['facets']['ranges']) ? :$this->addFacetsRange();

        if (!empty($results)) {
            $values = $results['hits']['hits'];

            foreach ($this->facetTags as $keyFacet => $value) {
                if (!empty($value['field_name'])) {
                    if (!empty($results['aggregations'][$value['field_name']])) {
                        $aggragation = $results['aggregations'][$value['field_name']]['buckets'];

                        foreach ($this->facets[$keyFacet] as $keyElement => $valueFacet) {
                            foreach ($aggragation as $keyAggregation => $aggregationValue) {
                                if (strtolower($valueFacet['key']) === $aggregationValue['key']) {
                                    $this->facets[$keyFacet][$keyElement]['doc_count'] = $aggregationValue['doc_count'];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->facets;
    }
    /**
     * Dispatch Filter
     *
     * @param array $paramFilter
     *
     * @return array
     */
    public function dispatchFilter($paramFilter)
    {
        $facet = [];
        foreach ($this->indexConfig['facets']['tags_relation'] as $keyRelation => $relation) {
            $facetValues = array_keys($this->getSearchListValue($relation));

            foreach ($paramFilter as $key => $value) {
                $index = key($value);
                if (in_array($value[$index], $facetValues)) {
                    $facet[$keyRelation][] = strtolower($value[$index]);
                }
            }
        }

        return $facet;
    }

    /**
     * Get facets Tag
     *
     * @return void
     */
    public function getFacetsTag()
    {
        $this->facetTags['taxons_four'] = [
            "display_name" => "AVAILABLE FOR NOTTURNO",
            "field_name" => "field_concert_notturno",
            "type" => "terms",
            "value" => "",
            "values" => [],
        ];

        return $this->facetTags;
    }

    /**
     * Add Tag Relation
     *
     * @return void
     */
    private function addFacetTagRelation()
    {

        foreach ($this->indexConfig['facets']['tags_relation'] as $keyRelation => $relation) {
            //$taxonomy = $this->getSearchListValue('offer.seller.commercialName.keyword', $this->getBucketKey($results, 'seller'));
            //TODO add $keyBuckets.
            $taxonomy = $this->getSearchListValue($relation);
            $this->facets['taxons_'.$keyRelation] = [];

            if ($taxonomy) {
                foreach ($taxonomy as $key => $taxon) {
                    $currentTaxon = $taxon;
                    $this->facets['taxons_'.$keyRelation][$currentTaxon] = [
                        "key" => $key,
                        "label" => $currentTaxon,
                        "doc_count" => 0,
                    ];
                }
                $this->facetTags['taxons_'.$keyRelation] = [
                    "display_name" => $relation['display_name'],
                    "field_name" => $keyRelation,
                    "type" => $relation['tag_type'],
                    "value" => "",
                    "values" => [],
                ];
            }
        }
    }

    /**
     * Add Facet Date.
     */
    private function addFacetsDate()
    {
        foreach ($this->indexConfig['facets']['dates'] as $keyDate => $date) {
            $this->facets['date_'.$keyDate][$keyDate] = [
                "key" => $keyDate,
            ];
            $this->facetTags['date_'.$keyDate] = [
                "display_name" => $date['display_name'],
                "field_name" => $keyDate,
                "type" => $date['tag_type'],
                "value" => "",
                "values" => [],
            ];
        }
    }

    /**
     * Add Facets. Range
     *
     */
    private function addFacetsRange()
    {
        foreach ($this->indexConfig['facets']['ranges'] as $keyRange => $range) {
            $this->facets['range-'.$range['tag_type'].'_'.$keyRange][$keyRange] = [
                "key" => $keyRange,
            ];
            $this->facetTags['range-'.$range['tag_type'].'_'.$keyRange] = [
                "display_name" => $range['display_name'],
                "field_name" => $keyRange,
                "type" => $range['tag_type'],
                "value" => "",
                "values" => [],
            ];
        }
    }

    /**
     * Get serach list value.
     *
     * @param array $config
     * @param array $list
     *
     * @return void
     */
    private function getSearchListValue($config, $list = [])
    {

        $searchRequest = new SearchRequest();
        $searchRequest->setSize(1000);
        $searchRequest->setType($config['type']);
        $index = new Index($config['index_name'], new EsClient($this->host));
        $results = [];

        if (empty($list)) {
            $results = $index->search($searchRequest);
        } else {
            $parameter = [];
            $parameter['body'] = $this->getBodyListSearch($list);
            //$results = $client->search($Parameter);
        }

        $taxonomy = [];

        if ($results) {
            $values = $results['hits']['hits'];

            foreach ($values as $document) {
                $keyTaxonomy = $document['_source']['id'];
                $label = $document['_source'][$config['field_name']];
                $taxonomy[$keyTaxonomy] = $label;
            }
        }

        return $taxonomy;
    }

    /**
     * Get bucket key
     *
     * @param array  $results
     * @param string $field
     *
     * @return array
     */
    private function getBucketKey($results, $field)
    {
        $keyBuckets = [];

        if (isset($results['aggregations'][$field]['buckets']) && !empty($results['aggregations'][$field]['buckets'])) {
            foreach ($results['aggregations'][$field]['buckets'] as $value) {
                $keyBuckets[] = $value['key'];
            }
        }

        return $keyBuckets;
    }

    /**
     * Get field list values costum.
     *
     * @param string $type
     * @param string $field
     *
     * @return array
     */
    private function getFieldListValuesCustom($type, $field)
    {
        //TODO
        return [
            'a', 'b', 'c',

        ];
    }
}
