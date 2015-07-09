<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\NotInBuilder;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class NotInBuilderTest extends AbstractTestCase
{
    protected $expression;
    protected $builder;

    public function setUp()
    {
        $this->builder = new NotInBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testRetrievingWherePartsReturnsSpecificationArrayOfIdentifierAndValuesAndArrayOfTypes()
    {
        $in = new NotIn();
        $in->setIdentifier('foo.bar')
            ->setValueSet([1, 2, 3]);

        $this->assertEquals(
            [[
                '%s NOT IN (%s, %s, %s)',
                [
                    new ExpressionParameter('foo.bar', NotIn::TYPE_IDENTIFIER),
                    new ExpressionParameter(1,         NotIn::TYPE_VALUE),
                    new ExpressionParameter(2,         NotIn::TYPE_VALUE),
                    new ExpressionParameter(3,         NotIn::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($in, $this->context)
        );
    }

    public function testGetExpressionDataWithSubselect()
    {
        $select = new Select;
        $in = new NotIn('foo', $select);

        $this->assertEquals(
            [[
                '%s NOT IN %s',
                [
                    new ExpressionParameter('foo',   $in::TYPE_IDENTIFIER),
                    new ExpressionParameter($select, $in::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($in, $this->context)
        );
    }

    public function testGetExpressionDataWithSubselectAndIdentifier()
    {
        $select = new Select;
        $in = new NotIn('foo', $select);

        $this->assertEquals(
            [[
                '%s NOT IN %s',
                [
                    new ExpressionParameter('foo',   $in::TYPE_IDENTIFIER),
                    new ExpressionParameter($select, $in::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($in, $this->context)
        );
    }

    public function testGetExpressionDataWithSubselectAndArrayIdentifier()
    {
        $select = new Select;
        $in = new NotIn(['foo', 'bar'], $select);

        $this->assertEquals(
            [[
                '(%s, %s) NOT IN %s',
                [
                    new ExpressionParameter('foo',   $in::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',   $in::TYPE_IDENTIFIER),
                    new ExpressionParameter($select, $in::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($in, $this->context)
        );
    }
}
