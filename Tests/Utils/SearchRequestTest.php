<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 09/07/17
 * Time: 17:25
 */

namespace EscapeHither\SearchManagerBundle\Tests\Utils;
use EscapeHither\SearchManagerBundle\Utils\SearchRequest;

class SearchRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateRequest()
    {
        $result ['body']['query']['filtered']['query']['query_string']['query'] = '*';
        $request = new SearchRequest();
        $this->assertEquals($result, $request->generateRequest());
    }
    public function testAddFilterTerm(){
        $request['term'][]['term']= ['field_one'=>'value'];
        $result['body']['query']['filtered']['query']['bool']['must'][] = $request['term'];
        $request = new SearchRequest();
        $request->addFilter('term', ['field_one'=>'value'] );
        $this->assertEquals($result, $request->generateRequest());
    }

}
