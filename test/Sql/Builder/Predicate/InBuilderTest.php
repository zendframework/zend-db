<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\InBuilder;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Combine;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class InBuilderTest extends AbstractTestCase
{
    protected $builder;

    public function setUp()
    {
        $this->builder = new InBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testRetrievingWherePartsReturnsSpecificationArrayOfIdentifierAndValuesAndArrayOfTypes()
    {
        $in = new In();
        $in->setIdentifier('foo.bar')
            ->setValueSet([1, 2, 3]);

        $this->assertEquals(
            [[
                '%s IN (%s, %s, %s)',
                [
                    new ExpressionParameter('foo.bar', In::TYPE_IDENTIFIER),
                    new ExpressionParameter(1,         In::TYPE_VALUE),
                    new ExpressionParameter(2,         In::TYPE_VALUE),
                    new ExpressionParameter(3,         In::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($in, $this->context)
        );

        $in->setIdentifier('foo.bar')
            ->setValueSet([
                [1=>In::TYPE_LITERAL],
                [2=>In::TYPE_VALUE],
                [3=>In::TYPE_LITERAL],
            ]);

        $this->assertEquals(
            [[
                '%s IN (%s, %s, %s)',
                [
                    new ExpressionParameter('foo.bar', In::TYPE_IDENTIFIER),
                    new ExpressionParameter(1,         In::TYPE_LITERAL),
                    new ExpressionParameter(2,         In::TYPE_VALUE),
                    new ExpressionParameter(3,         In::TYPE_LITERAL),
                ],
            ]],
            $this->builder->getExpressionData($in, $this->context)
        );
    }

    public function testGetExpressionDataWithSubselect()
    {
        $select = new Select;
        $in = new In('foo', $select);

        $this->assertEquals(
            [[
                '%s IN %s',
                [
                    new ExpressionParameter('foo',   $in::TYPE_IDENTIFIER),
                    new ExpressionParameter($select, $in::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($in, $this->context)
        );

        $combine = new Combine;
        $in = new In('foo', $combine);

        $this->assertEquals(
            [[
                '%s IN %s',
                [
                    new ExpressionParameter('foo',    $in::TYPE_IDENTIFIER),
                    new ExpressionParameter($combine, $in::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($in, $this->context)
         );
    }

    public function testGetExpressionDataWithSubselectAndIdentifier()
    {
        $select = new Select;
        $in = new In('foo', $select);

        $this->assertEquals(
            [[
                '%s IN %s',
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
        $in = new In(['foo', 'bar'], $select);

        $this->assertEquals(
            [[
                '(%s, %s) IN %s',
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
