<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 14/10/17
 * Time: 23:05
 */

namespace EscapeHither\SearchManagerBundle\Utils;


class SearchRequest
{
    protected $request;

    public function __construct()
    {

    }

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

    protected function checkTypeFilter($type)
    {


    }


}