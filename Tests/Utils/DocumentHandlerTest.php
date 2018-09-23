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

use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;

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
        $document = $documentHandler->createDocument();
        $result = [
          'name'=>'alain',
          'age'=>4,
          'sportsman'=>null,
        ];

        $this->assertEquals($result, $document->getField());
    }
}
