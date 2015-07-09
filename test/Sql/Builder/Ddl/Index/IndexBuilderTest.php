<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Ddl\Index;

use Zend\Db\Sql\Builder\Builder;
use Zend\Db\Sql\Builder\sql92\Ddl\Index\IndexBuilder;
use Zend\Db\Sql\Ddl\Index\Index;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class IndexBuilderTest extends AbstractTestCase
{
    protected $builder;

    public function setUp()
    {
        $this->builder = new IndexBuilder(new Builder);
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Index\Index::getExpressionData
     */
    public function testGetExpressionData()
    {
        $uk = new Index('foo', 'my_uk');
        $this->assertEquals(
            [[
                'INDEX %s(%s)',
                [
                    new ExpressionParameter('my_uk', $uk::TYPE_IDENTIFIER),
                    new ExpressionParameter('foo',   $uk::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($uk, $this->context)
        );
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Index\Index::getExpressionData
     */
    public function testGetExpressionDataWithLength()
    {
        $key = new Index(['foo', 'bar'], 'my_uk', [10, 5]);
        $this->assertEquals(
            [[
                'INDEX %s(%s(10), %s(5))',
                [
                    new ExpressionParameter('my_uk', $key::TYPE_IDENTIFIER),
                    new ExpressionParameter('foo',   $key::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',   $key::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($key, $this->context)
        );
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Index\Index::getExpressionData
     */
    public function testGetExpressionDataWithLengthUnmatched()
    {
        $key = new Index(['foo', 'bar'], 'my_uk', [10]);
        $this->assertEquals(
            [[
                'INDEX %s(%s(10), %s)',
                [
                    new ExpressionParameter('my_uk', $key::TYPE_IDENTIFIER),
                    new ExpressionParameter('foo',   $key::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',   $key::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($key, $this->context)
        );
    }
}
