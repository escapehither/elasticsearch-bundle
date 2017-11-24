<?php
/**
 * This file is part of the saerch-manager bundle package.
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


    public function addFilter($type, array $fields)
    {
        foreach ($fields as $fieldName => $value) {
            $this->request['body']['query']['filtered']['query']['bool']['must'][][][$type] = [$fieldName => $value];
        }
    }

    /**
     * Add From parameter.
     * @param int $value
     */
     public function addFrom($value){
         if (is_int($value)){
             $this->request['from']= $value;
         }else{
             //TODO ADD ERROR EXEPCTION
         }

     }

    /**
     * Add size parameter.
     * @param int $value
     */
    public function addSize($value){
        if (is_int($value)){
            $this->request['size']= $value;
        }else{

        }

    }

    protected function checkTypeFilter($type)
    {


    }


}