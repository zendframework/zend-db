<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Predicate;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\ExpressionParameter;

class OperatorTest extends TestCase
{
    public function testEmptyConstructorYieldsNullLeftAndRightValues()
    {
        $operator = new Operator();
        $this->assertNull($operator->getLeft());
        $this->assertNull($operator->getRight());
    }

    public function testCanPassAllValuesToConstructor()
    {
        $operator = new Operator(['bar', Operator::TYPE_VALUE], '>=', ['foo.bar', Operator::TYPE_IDENTIFIER]);
        $this->assertEquals(Operator::OP_GTE, $operator->getOperator());
        $this->assertEquals('bar', $operator->getLeft()->getValue());
        $this->assertEquals('foo.bar', $operator->getRight()->getValue());
        $this->assertEquals(Operator::TYPE_VALUE, $operator->getLeft()->getType());
        $this->assertEquals(Operator::TYPE_IDENTIFIER, $operator->getRight()->getType());

        $operator = new Operator(['bar'=>Operator::TYPE_VALUE], '>=', ['foo.bar'=>Operator::TYPE_IDENTIFIER]);
        $this->assertEquals(Operator::OP_GTE, $operator->getOperator());
        $this->assertEquals(new ExpressionParameter('bar', Operator::TYPE_VALUE), $operator->getLeft());
        $this->assertEquals(new ExpressionParameter('foo.bar', Operator::TYPE_IDENTIFIER), $operator->getRight());
        $this->assertEquals(Operator::TYPE_VALUE, $operator->getLeft()->getType());
        $this->assertEquals(Operator::TYPE_IDENTIFIER, $operator->getRight()->getType());

        $operator = new Operator('bar', '>=', 0);
        $this->assertEquals(0, $operator->getRight()->getValue());
    }

    public function testLeftIsMutable()
    {
        $operator = new Operator();
        $operator->setLeft('foo.bar');
        $this->assertEquals('foo.bar', $operator->getLeft()->getValue());
    }

    public function testRightIsMutable()
    {
        $operator = new Operator();
        $operator->setRight('bar');
        $this->assertEquals('bar', $operator->getRight()->getValue());
    }

    public function testOperatorIsMutable()
    {
        $operator = new Operator();
        $operator->setOperator(Operator::OP_LTE);
        $this->assertEquals(Operator::OP_LTE, $operator->getOperator());
    }
}
