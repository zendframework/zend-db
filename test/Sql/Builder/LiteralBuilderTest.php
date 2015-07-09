<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\Builder\sql92\LiteralBuilder;
use Zend\Db\Sql\Predicate\Literal;
use Zend\Db\Sql\Literal as BaseLiteral;
use Zend\Db\Sql\Builder\Context;

class LiteralBuilderTest extends AbstractTestCase
{
    protected $builder;

    public function setUp()
    {
        $this->builder = new LiteralBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testGetExpressionData()
    {
        $literal = new Literal('bar');
        $this->assertEquals(
            [[
                'bar',
                [],
            ]],
            $this->builder->getExpressionData($literal, $this->context)
        );
    }

    public function testGetExpressionDataWillEscapePercent()
    {
        $literal = new BaseLiteral('X LIKE "foo%"');
        $this->assertEquals([[
                'X LIKE "foo%%"',
                [],
            ]],
            $this->builder->getExpressionData($literal, $this->context)
        );

        $literal = new BaseLiteral('bar');
        $this->assertEquals(
            [[
                'bar',
                [],
            ]],
            $this->builder->getExpressionData($literal, $this->context)
        );
    }
}
