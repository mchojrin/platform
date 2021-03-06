<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Config\Definition\Builder;

use Oro\Bundle\NavigationBundle\Config\Definition\Builder\MenuNodeDefinition;

class MenuNodeDefinitionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $builder;

    /**
     * @var MenuNodeDefinition
     */
    protected $definition;

    protected function setUp()
    {
        $this->builder = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Config\Definition\Builder\MenuTreeBuilder')
            ->setMethods(
                array('node', 'children', 'scalarNode', 'end', 'menuNode', 'menuNodeHierarchy', 'defaultValue')
            )
            ->getMock();
        $this->definition = new MenuNodeDefinition('test');
        $this->definition->setBuilder($this->builder);
    }

    public function testMenuNodeHierarchyZeroDepth()
    {
        $this->builder->expects($this->never())
            ->method('node');

        $this->assertInstanceOf(
            'Oro\Bundle\NavigationBundle\Config\Definition\Builder\MenuNodeDefinition',
            $this->definition->menuNodeHierarchy(0)
        );
    }

    public function testMenuNodeHierarchyNonZeroDepth()
    {
        $this->builder->expects($this->any())
            ->method('node')
            ->will($this->returnSelf());

        $this->builder->expects($this->any())
            ->method('children')
            ->will($this->returnSelf());

        $this->builder->expects($this->any())
            ->method('scalarNode')
            ->will($this->returnSelf());

        $this->builder->expects($this->any())
            ->method('end')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('menuNode')
            ->with('children')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('menuNodeHierarchy')
            ->with(9)
            ->will($this->returnSelf());
        $this->builder->expects($this->any())
            ->method('defaultValue')
            ->will($this->returnSelf());

        $this->definition->menuNodeHierarchy(10);
    }
}
