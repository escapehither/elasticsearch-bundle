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

use EscapeHither\SearchManagerBundle\Tests\Utils\dataTest;
use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;
//use EscapeHither\SearchManagerBundle\Tests\Utils\DataTest;
class DocumentHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateDocument()
    {

        $data = new DataTest();
        $data->setName('alain');
        $data->setAge(4);
        $documentHandler = new DocumentHandler($data,['type'=>'test']);
        $document = $documentHandler->createDocument();
        $result = [
          'name'=>'alain',
          'age'=>4,
          'sportsman'=>NULL,
        ];
        $this->assertEquals($result, $document->getField());


    }
}
