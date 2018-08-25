<?php
/**
 * This file is part of the search-manager bundle package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 14/10/17
 * Time: 23:05
 */

namespace EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp;

/**
 * Class SearchRequest
 * @package EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp
 */
class SearchRequest implements SearchReQuestInterface
{
    protected $request;
    protected $index;

    /**
     * @return mixed
     */
    public function generateRequest()
    {
        if (empty($this->request)) {
             $this->request['body']['query']['bool']['must']['query_string']['query'] = '*';

            return $this->request;
        } else {
            return $this->request;
        }
    }

    /**
     * Add query string.
     *
     * @param string $string The query string.
     * @return void
     */
    public function setString($string)
    {
        $this->request['body']['query']['bool']['must']['query_string']['query'] = $string.'*';
        $this->request['body']['query']['bool']['must']['query_string']['fields'] = [
            '_all',
            '*.asciifolding',
        ];
    }

    /**
     * Add a filter.
     *
     * @param string $type   The filter type.
     * @param array  $fields The filter fields.
     *
     * @return void
     */
    public function addFilter($type, array $fields)
    {
        foreach ($fields as $fieldName => $value) {
            //Before
            //$this->request['body']['query']['filtered']['query']['bool']['must'][][][$type] = [$fieldName => $value];
            // After.
            if ("range" === $type) {
                if ($value['gte'] != "" and $value['lte'] != "") {
                    $this->request['body']['query']['bool']['filter'][][][$type] = [$fieldName => $value];
                }
            } else {
                empty($value)?:$this->request['body']['query']['bool']['filter'][][][$type] = [$fieldName => $value];
            }
        }
    }

    /**
     * Add Aggregate.
     *
     * @param array $fields The aggs fields.
     *
     * @return void
     */
    public function addAggs(array $fields)
    {
        foreach ($fields as $fieldName => $value) {
            // TODO  require field and size.
            $this->request['body']['aggs'][$value['name']]['terms'] =  [
                'field' => $fieldName,
                'size' => $value['size'],
            ];
        }
    }

    /**
     * Set From parameter.
     *
     * @param int $value
     */
    public function setFrom($value)
    {
        if (is_int($value)) {
            $this->request['from'] = $value;
        } else {
            throw new \LogicException('from parameter must be an integer');
        }
    }

    /**
     * Set The index to search upon.
     *
     * @param Index $index The index.
     *
     * @return void
     */
    public function setIndex(Index $index)
    {
        $this->request['index'] = $index->getName();
        $this->index = $index;
    }

    /**
     * Set The index type to search upon.
     *
     * @param string $type The request index type.
     *
     * @return void
     */
    public function setType($type)
    {
        $this->request['type'] = $type;
    }

    /**
     * set size parameter.
     *
     * @param int $value
     */
    public function setSize($value)
    {
        if (is_int($value)) {
            $this->request['size'] = $value;
        } else {
            throw new \LogicException('size parameter must be an integer');
        }
    }

    protected function checkTypeFilter($type)
    {
      // TODO check  the filter.
        return true;
    }
}
