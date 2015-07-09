<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\PredicateBuilder;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class PredicateBuilderTest extends AbstractTestCase
{
    protected $expression;
    protected $builder;

    public function setUp()
    {
        $this->expression = new Predicate;
        $this->builder = new PredicateBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testEqualToCreatesOperatorPredicate()
    {
        $predicate = new Predicate();
        $predicate->equalTo('foo.bar', 'bar');
        $this->assertEquals(
            [[
                '%s = %s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',     Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testNotEqualToCreatesOperatorPredicate()
    {
        $predicate = new Predicate();
        $predicate->notEqualTo('foo.bar', 'bar');
        $this->assertEquals(
            [[
                '%s != %s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',     Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testLessThanCreatesOperatorPredicate()
    {
        $predicate = new Predicate();
        $predicate->lessThan('foo.bar', 'bar');
        $this->assertEquals(
            [[
                '%s < %s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',     Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testGreaterThanCreatesOperatorPredicate()
    {
        $predicate = new Predicate();
        $predicate->greaterThan('foo.bar', 'bar');
        $this->assertEquals(
            [[
                '%s > %s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',     Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testLessThanOrEqualToCreatesOperatorPredicate()
    {
        $predicate = new Predicate();
        $predicate->lessThanOrEqualTo('foo.bar', 'bar');
        $this->assertEquals(
            [[
                '%s <= %s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',     Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testGreaterThanOrEqualToCreatesOperatorPredicate()
    {
        $predicate = new Predicate();
        $predicate->greaterThanOrEqualTo('foo.bar', 'bar');
        $this->assertEquals(
            [[
                '%s >= %s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',     Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testLikeCreatesLikePredicate()
    {
        $predicate = new Predicate();
        $predicate->like('foo.bar', 'bar%');
        $this->assertEquals(
            [[
                '%1$s LIKE %2$s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar%',    Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testNotLikeCreatesLikePredicate()
    {
        $predicate = new Predicate();
        $predicate->notLike('foo.bar', 'bar%');
        $this->assertEquals(
            [[
                '%1$s NOT LIKE %2$s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar%',    Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testLiteralCreatesLiteralPredicate()
    {
        $predicate = new Predicate();
        $predicate->literal('foo.bar = ?', 'bar');
        $this->assertEquals(
            [[
                'foo.bar = %s',
                [
                    new ExpressionParameter('bar', Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testIsNullCreatesIsNullPredicate()
    {
        $predicate = new Predicate();
        $predicate->isNull('foo.bar');
        $this->assertEquals(
            [[
                '%1$s IS NULL',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testIsNotNullCreatesIsNotNullPredicate()
    {
        $predicate = new Predicate();
        $predicate->isNotNull('foo.bar');
        $this->assertEquals(
            [[
                '%1$s IS NOT NULL',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testInCreatesInPredicate()
    {
        $predicate = new Predicate();
        $predicate->in('foo.bar', ['foo', 'bar']);
        $this->assertEquals(
            [[
                '%s IN (%s, %s)',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('foo',     Predicate::TYPE_VALUE),
                    new ExpressionParameter('bar',     Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testNotInCreatesNotInPredicate()
    {
        $predicate = new Predicate();
        $predicate->notIn('foo.bar', ['foo', 'bar']);
        $this->assertEquals(
            [[
                '%s NOT IN (%s, %s)',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter('foo',     Predicate::TYPE_VALUE),
                    new ExpressionParameter('bar',     Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testBetweenCreatesBetweenPredicate()
    {
        $predicate = new Predicate();
        $predicate->between('foo.bar', 1, 10);
        $this->assertEquals(
            [[
                '%1$s BETWEEN %2$s AND %3$s',
                [
                    new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    new ExpressionParameter(1,         Predicate::TYPE_VALUE),
                    new ExpressionParameter(10,        Predicate::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testCanChainPredicateFactoriesBetweenOperators()
    {
        $predicate = new Predicate();
        $predicate->isNull('foo.bar')
                  ->or
                  ->isNotNull('bar.baz')
                  ->and
                  ->equalTo('baz.bat', 'foo');
        $this->assertEquals(
            [
                [
                    '%1$s IS NULL',
                    [
                        new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    ],
                ],
                ' OR ',
                [
                    '%1$s IS NOT NULL',
                    [
                        new ExpressionParameter('bar.baz', Predicate::TYPE_IDENTIFIER),
                    ],
                ],
                ' AND ',
                [
                    '%s = %s',
                    [
                        new ExpressionParameter('baz.bat', Predicate::TYPE_IDENTIFIER),
                        new ExpressionParameter('foo',     Predicate::TYPE_VALUE),
                    ],
                ],
            ],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    public function testCanNestPredicates()
    {
        $predicate = new Predicate();
        $predicate->isNull('foo.bar')
                  ->nest()
                  ->isNotNull('bar.baz')
                  ->and
                  ->equalTo('baz.bat', 'foo')
                  ->unnest();
        $this->assertEquals(
            [
                [
                    '%1$s IS NULL',
                    [
                        new ExpressionParameter('foo.bar', Predicate::TYPE_IDENTIFIER),
                    ],
                ],
                ' AND ',
                '(',
                [
                    '%1$s IS NOT NULL',
                    [
                        new ExpressionParameter('bar.baz', Predicate::TYPE_IDENTIFIER),
                    ],
                ],
                ' AND ',
                [
                    '%s = %s',
                    [
                        new ExpressionParameter('baz.bat', Predicate::TYPE_IDENTIFIER),
                        new ExpressionParameter('foo',     Predicate::TYPE_VALUE),
                    ],
                ],
                ')',
            ],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    /**
     * @testdox Unit test: Test expression() is chainable and returns proper values
     */
    public function testExpression()
    {
        $predicate = new Predicate;

        // is chainable
        $this->assertSame($predicate, $predicate->expression('foo = ?', 0));
        // with parameter
        $this->assertEquals(
            [[
                'foo = %s',
                [
                    new ExpressionParameter(0, Predicate::TYPE_VALUE)
                ]
            ]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }

    /**
     * @testdox Unit test: Test expression() allows null $parameters
     */
    public function testExpressionNullParameters()
    {
        $predicate = new Predicate;

        $predicate->expression('foo = bar');
        $predicates = $predicate->getPredicates();
        $expression = $predicates[0][1];
        $this->assertEquals(
            [new ExpressionParameter(null, Predicate::TYPE_VALUE)],
            $expression->getParameters()
        );
    }

    /**
     * @testdox Unit test: Test literal() is chainable, returns proper values, and is backwards compatible with 2.0.*
     */
    public function testLiteral()
    {
        $predicate = new Predicate;

        // is chainable
        $this->assertSame($predicate, $predicate->literal('foo = bar'));
        // with parameter
        $this->assertEquals(
            [['foo = bar', []]],
            $this->builder->getExpressionData($predicate, $this->context)
        );

        // test literal() is backwards-compatible, and works with with parameters
        $predicate = new Predicate;
        $predicate->expression('foo = ?', 'bar');
        // with parameter
        $this->assertEquals(
            [['foo = %s', [new ExpressionParameter('bar', ExpressionInterface::TYPE_VALUE)]]],
            $this->builder->getExpressionData($predicate, $this->context)
        );

        // test literal() is backwards-compatible, and works with with parameters, even 0 which tests as false
        $predicate = new Predicate;
        $predicate->expression('foo = ?', 0);
        // with parameter
        $this->assertEquals(
            [['foo = %s', [new ExpressionParameter(0, ExpressionInterface::TYPE_VALUE)]]],
            $this->builder->getExpressionData($predicate, $this->context)
        );
    }
}
