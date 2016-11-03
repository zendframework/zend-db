<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\ExpressionParameter;

/**
 * This is a unit testing test case.
 * A unit here is a method, there will be at least one test per method
 *
 * Expression is a value object with no dependencies/collaborators, therefore, no fixure needed
 */
class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Expression::setExpression
     * @return Expression
     */
    public function testSetExpression()
    {
        $expression = new Expression();
        $return = $expression->setExpression('Foo Bar');
        $this->assertSame($expression, $return);
        return $return;
    }

    /**
     * @covers Zend\Db\Sql\Expression::setExpression
     */
    public function testSetExpressionException()
    {
        $expression = new Expression();
        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException', 'Supplied expression must be a string.');
        $expression->setExpression(null);
    }

    /**
     * @covers Zend\Db\Sql\Expression::getExpression
     * @depends testSetExpression
     */
    public function testGetExpression(Expression $expression)
    {
        $this->assertEquals('Foo Bar', $expression->getExpression());
    }

    /**
     * @covers Zend\Db\Sql\Expression::setParameters
     */
    public function testSetParameters()
    {
        $expression = new Expression();
        $return = $expression->setParameters('foo');
        $this->assertSame($expression, $return);
        return $return;
    }

    /**
     * @covers Zend\Db\Sql\Expression::setParameters
     */
    public function testSetParametersException()
    {
        $expression = new Expression('', 'foo');

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException', 'Expression parameters must be a scalar or array.');
        $expression->setParameters(null);
    }

    /**
     * @covers Zend\Db\Sql\Expression::getParameters
     * @depends testSetParameters
     */
    public function testGetParameters(Expression $expression)
    {
        $this->assertEquals(
            [
                new ExpressionParameter('foo', Expression::TYPE_VALUE)
            ],
            $expression->getParameters()
        );
    }

    /**
     * @covers Zend\Db\Sql\Expression::__construct
     */
    public function testConstructorWithLiteralZero()
    {
        $expression = new Expression('0');
        $this->assertSame('0', $expression->getExpression());
    }

    /**
     * @group 7407
     */
    public function testGetExpressionPreservesPercentageSignInFromUnixtime()
    {
        $expressionString = 'FROM_UNIXTIME(date, "%Y-%m")';
        $expression       = new Expression($expressionString);

        $this->assertSame($expressionString, $expression->getExpression());
    }
}
