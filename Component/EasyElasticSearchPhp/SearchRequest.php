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
     * SearchRequest constructor.
     *
     */
    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function generateRequest()
    {
        if (empty($this->request)) {
             $this->request['body']['query']['filtered']['query']['query_string']['query'] = '*';
            return $this->request;
        } else {
            return $this->request;
        }

    }

    public function setString($string){
        $this->request['body']['query']['filtered']['query']['query_string']['query'] = $string.'*';
        $this->request['body']['query']['filtered']['query']['query_string']['fields'] = [
            '_all',
            '*.asciifolding',
        ];
    }

    public function addFilter($type, array $fields)
    {
        foreach ($fields as $fieldName => $value) {
            $this->request['body']['query']['filtered']['query']['bool']['must'][][][$type] = [$fieldName => $value];
        }
    }

    /**
     * Set From parameter.
     * @param int $value
     */
     public function setFrom($value){
         if (is_int($value)){
             $this->request['from']= $value;
         }else{
             throw new \LogicException('from parameter must be an integer');
         }

     }
    public function setIndex(Index $index){
        $this->request['index'] = $index->getName();
        $this->index = $index;

    }

    /**
     * set size parameter.
     * @param int $value
     */
    public function setSize($value){
        if (is_int($value)){
            $this->request['size']= $value;
        }else{
            throw new \LogicException('size parameter must be an integer');
        }

    }

    protected function checkTypeFilter($type)
    {


    }


}