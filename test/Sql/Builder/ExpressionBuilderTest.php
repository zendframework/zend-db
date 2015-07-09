<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\Builder\sql92\ExpressionBuilder;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Expression as BaseExpression;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;

class ExpressionBuilderTest extends AbstractTestCase
{
    protected $expression;
    protected $builder;

    public function setUp()
    {
        $this->builder = new ExpressionBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testRetrievingWherePartsReturnsSpecificationArrayOfLiteralAndParametersAndArrayOfTypes()
    {
        $expression = new Expression();
        $expression->setExpression('foo.bar = ? AND id != ?')
                        ->setParameters(['foo', 'bar']);

        $this->assertEquals(
            [[
                'foo.bar = %s AND id != %s',
                [
                    new ExpressionParameter('foo', Expression::TYPE_VALUE),
                    new ExpressionParameter('bar', Expression::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($expression, $this->context)
        );
    }

    /**
     * @covers Zend\Db\Sql\Expression::getExpressionData
     */
    public function testGetExpressionData()
    {
        $expression = new BaseExpression(
            'X SAME AS ? AND Y = ? BUT LITERALLY ?',
            [
                ['foo',        Expression::TYPE_IDENTIFIER],
                [5,            Expression::TYPE_VALUE],
                ['FUNC(FF%X)', Expression::TYPE_LITERAL],
            ]
        );
        $this->assertEquals(
            [[
                'X SAME AS %s AND Y = %s BUT LITERALLY %s',
                [
                    new ExpressionParameter('foo',        Expression::TYPE_IDENTIFIER),
                    new ExpressionParameter(5,            Expression::TYPE_VALUE),
                    new ExpressionParameter('FUNC(FF%X)', Expression::TYPE_LITERAL),
                ],
            ]],
            $this->builder->getExpressionData($expression, $this->context)
        );

        $expression = new BaseExpression(
            'X SAME AS ? AND Y = ? BUT LITERALLY ?',
            [
                ['foo'        => Expression::TYPE_IDENTIFIER],
                [5            => Expression::TYPE_VALUE],
                ['FUNC(FF%X)' => Expression::TYPE_LITERAL],
            ]
        );
        $this->assertEquals(
            [[
                'X SAME AS %s AND Y = %s BUT LITERALLY %s',
                [
                    new ExpressionParameter('foo',        Expression::TYPE_IDENTIFIER),
                    new ExpressionParameter(5,            Expression::TYPE_VALUE),
                    new ExpressionParameter('FUNC(FF%X)', Expression::TYPE_LITERAL),
                ],
            ]],
            $this->builder->getExpressionData($expression, $this->context)
        );
    }

    public function testGetExpressionDataWillEscapePercent()
    {
        $expression = new BaseExpression('X LIKE "foo%"');
        $this->assertEquals(
            ['X LIKE "foo%%"'],
            $this->builder->getExpressionData($expression, $this->context)
        );
    }

    public function testNumberOfReplacemensConsidersWhenSameVariableIsUsedManyTimes()
    {
        $expression = new Expression('uf.user_id = :user_id OR uf.friend_id = :user_id', ['user_id' => 1]);
        $this->builder->getExpressionData($expression, $this->context);
    }
}
