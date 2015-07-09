<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\BetweenBuilder;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Predicate\Between;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class BetweenBuilderTest extends AbstractTestCase
{
    protected $expression;
    protected $builder;

    public function setUp()
    {
        $this->expression = new Between;
        $this->builder = new BetweenBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Between::getExpressionData
     */
    public function testRetrievingWherePartsReturnsSpecificationArrayOfIdentifierAndValuesAndArrayOfTypes()
    {
        $this->expression->setIdentifier('foo.bar')
                      ->setMinValue(10)
                      ->setMaxValue(19);

        $this->assertEquals(
            [[
                '%1$s BETWEEN %2$s AND %3$s',
                [
                    new ExpressionParameter('foo.bar', ExpressionInterface::TYPE_IDENTIFIER),
                    new ExpressionParameter(10,        ExpressionInterface::TYPE_VALUE),
                    new ExpressionParameter(19,        ExpressionInterface::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($this->expression, $this->context)
        );

        $this->expression->setIdentifier([10=>Between::TYPE_VALUE])
                      ->setMinValue(['foo.bar'=>Between::TYPE_IDENTIFIER])
                      ->setMaxValue(['foo.baz'=>Between::TYPE_IDENTIFIER]);

        $this->assertEquals(
            [[
                '%1$s BETWEEN %2$s AND %3$s',
                [
                    new ExpressionParameter(10,        Between::TYPE_VALUE),
                    new ExpressionParameter('foo.bar', Between::TYPE_IDENTIFIER),
                    new ExpressionParameter('foo.baz', Between::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($this->expression, $this->context)
        );
    }
}
