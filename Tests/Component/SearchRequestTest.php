<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Tests\Component;

use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\SearchRequest;

class SearchRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateRequest()
    {
        $result['body']['query']['bool']['must']['query_string']['query'] = '*';
        $request = new SearchRequest();
        $this->assertEquals($result, $request->generateRequest());
    }
    public function testAddFilter()
    {
        $request['term'][]['term']= ['field_one'=>'value'];
        $result['body']['query']['bool']['filter'][] = $request['term'];
        $request = new SearchRequest();
        $request->addFilter('term', ['field_one'=>'value']);
        $this->assertEquals($result, $request->generateRequest());
    }
}
