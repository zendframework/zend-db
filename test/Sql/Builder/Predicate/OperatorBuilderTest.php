<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\OperatorBuilder;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class OperatorBuilderTest extends AbstractTestCase
{
    protected $expression;
    protected $builder;

    public function setUp()
    {
        $this->expression = new Operator;
        $this->builder = new OperatorBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testRetrievingWherePartsReturnsSpecificationArrayOfLeftAndRightAndArrayOfTypes()
    {
        $this->expression->setLeft(['foo', Operator::TYPE_VALUE])
            ->setOperator('>=')
            ->setRight(['foo.bar', Operator::TYPE_IDENTIFIER]);

        $this->assertEquals(
            [[
                '%s >= %s',
                [
                    new ExpressionParameter('foo',     Operator::TYPE_VALUE),
                    new ExpressionParameter('foo.bar', Operator::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($this->expression, $this->context)
        );
    }
}
