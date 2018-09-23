<?php
/**
 * This file is part of the search-manager-bundle package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 21/10/17
 * Time: 23:16
 */

namespace EscapeHither\SearchManagerBundle\Tests\Component;

use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Index;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\EsClient;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexGetDefaultParameters()
    {
        $params = ['index' => 'index_test'];
        $params['body'] = [
          'settings' => [
            'analysis' => [
              'analyzer' => [
                'folding_analyzer' => [
                  'tokenizer' => "standard",
                  'filter' => ["standard", "asciifolding", "lowercase","word_delimiter"],
                ],
              ],
            ],
          ],
        ];
        $index = new Index('index_test', new EsClient());
        $this->assertEquals($params, $index->getDefaultParameters());
    }
}
