<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Tests\Utils;

use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Document;

class DocumentHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateDocument()
    {
        $data = new DataTest();
        $data->setName('alain');
        $data->setAge(4);
        $config = [
        "entity" => "EscapeHither\SearchManagerBundle\Tests\Utils\DataTest",
        "index_name" => "index_test",
        "type" => "data_test",
        "facets" => [
                "tags" => [
                    "categories" => [
                        "include" => []
                    ]
                ],
                "tags_relation" => [
                    "offer.seller.id" =>  []
                ],
                "ranges" => [
                    "age" => [
                        "field_name" => "age",
                        "display_name" => "age",
                        "tag_type" => "price",
                    ]
                ],
                "dates" => []
            ]
        ];
        
        $documentHandler = new DocumentHandler($data, $config);
        $result = [
          'name'=>'alain',
          'age'=>4,
          'sportsman'=>null,
        ];
        $tags = ["tags" => [
            "categories" => [
                "include" => []
            ]
            ]];
        $this->assertInstanceOf(Document::class, $documentHandler->createDocument());
        $this->assertEquals($result, $documentHandler->createDocumentFields());
        $this->assertEquals( $config, $documentHandler->getConfiguration());
    }
}
