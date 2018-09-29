<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
namespace EscapeHither\SearchManagerBundle\Tests;

use EscapeHither\SearchManagerBundle\EscapeHitherSearchManagerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class EscapeHitherSearchManagerBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['addCompilerPass'])
            ->getMock();
        $container->expects($this->exactly(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(CompilerPassInterface::class));
        $bundle = new EscapeHitherSearchManagerBundle();
        $bundle->build($container);
    }
}
