<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\IsNotNullBuilder;
use Zend\Db\Sql\Predicate\IsNotNull;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class IsNotNullBuilderTest extends AbstractTestCase
{
    protected $expression;
    protected $builder;

    public function setUp()
    {
        $this->expression = new IsNotNull;
        $this->builder = new IsNotNullBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testRetrievingWherePartsReturnsSpecificationArrayOfIdentifierAndArrayOfTypes()
    {
        $this->expression->setIdentifier('foo.bar');

        $this->assertEquals(
            [[
                '%1$s IS NOT NULL',
                [
                    new ExpressionParameter('foo.bar', IsNotNull::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($this->expression, $this->context)
        );
    }
}
