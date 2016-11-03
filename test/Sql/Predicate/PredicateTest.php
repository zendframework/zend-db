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
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Predicate\Literal as predicateLiteral;
use Zend\Db\Sql\Predicate\Expression as predicateExpression;
use Zend\Db\Sql\ExpressionParameter;

class PredicateTest extends TestCase
{
    /**
     * @covers Zend\Db\Sql\Predicate\Predicate::addPredicates
     * @covers Zend\Db\Sql\Predicate\Predicate::getPredicates
     */
    public function testWhereArgument1IsAssociativeArrayContainingReplacementCharacter()
    {
        $where = new Predicate;
        $predicates = $where
                ->addPredicates(['foo > ?' => 5])
                ->getPredicates();
        $this->assertEquals(1, count($predicates));
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Expression', $predicates[0][1]);
        $this->assertEquals(Predicate::OP_AND, $predicates[0][0]);
        $this->assertEquals('foo > ?', $predicates[0][1]->getExpression());
        $this->assertEquals(
            [
                new ExpressionParameter(5)
            ],
            $predicates[0][1]->getParameters()
        );
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Predicate::addPredicates
     * @covers Zend\Db\Sql\Predicate\Predicate::getPredicates
     */
    public function testWhereArgument1IsAssociativeArrayNotContainingReplacementCharacter()
    {
        $where = new Predicate;
        $predicates = $where
                ->addPredicates(['name' => 'Ralph', 'age' => 33])
                ->getPredicates();
        $this->assertEquals(2, count($predicates));

        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Operator', $predicates[0][1]);
        $this->assertEquals(Predicate::OP_AND, $predicates[0][0]);
        $this->assertEquals('name', $predicates[0][1]->getLeft()->getValue());
        $this->assertEquals('Ralph', $predicates[0][1]->getRight()->getValue());

        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Operator', $predicates[1][1]);
        $this->assertEquals(Predicate::OP_AND, $predicates[1][0]);
        $this->assertEquals('age', $predicates[1][1]->getLeft()->getValue());
        $this->assertEquals(33, $predicates[1][1]->getRight()->getValue());

        $where = new Predicate;
        $predicates = $where
                ->addPredicates(['x = y'])
                ->getPredicates();
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Literal', $predicates[0][1]);
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Predicate::addPredicates
     */
    public function testWhereArgument1IsAssociativeArrayIsPredicate()
    {
        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException', 'Using Predicate must not use string keys');
        $where = new Predicate;
        $where->addPredicates([
            'name' => new predicateLiteral("name = 'Ralph'"),
            'age' => new predicateExpression('age = ?', 33),
        ]);
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Predicate::addPredicates
     * @covers Zend\Db\Sql\Predicate\Predicate::getPredicates
     */
    public function testWhereArgument1IsIndexedArray()
    {
        $where = new Predicate;
        $predicates = $where
                ->addPredicates(['name = "Ralph"'])
                ->getPredicates();

        $this->assertEquals(1, count($predicates));
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Literal', $predicates[0][1]);
        $this->assertEquals(Predicate::OP_AND, $predicates[0][0]);
        $this->assertEquals('name = "Ralph"', $predicates[0][1]->getLiteral());
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Predicate::addPredicates
     * @covers Zend\Db\Sql\Predicate\Predicate::getPredicates
     */
    public function testWhereArgument1IsIndexedArrayArgument2IsOr()
    {
        $where = new Predicate;
        $predicates = $where
                ->addPredicates(['name = "Ralph"'], Predicate::OP_OR)
                ->getPredicates();

        $this->assertEquals(1, count($predicates));
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Literal', $predicates[0][1]);
        $this->assertEquals(Predicate::OP_OR, $predicates[0][0]);
        $this->assertEquals('name = "Ralph"', $predicates[0][1]->getLiteral());
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Predicate::addPredicates
     */
    public function testWhereArgument1IsClosure()
    {
        $where = new Predicate;
        $test = $this;
        $where->addPredicates(function ($what) use ($test, $where) {
            $test->assertSame($where, $what);
        });
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Predicate::addPredicates
     * @covers Zend\Db\Sql\Predicate\Predicate::getPredicates
     */
    public function testWhereArgument1IsString()
    {
        $where = new Predicate;
        $predicates = $where
                ->addPredicates('x = ?')
                ->getPredicates();

        $this->assertEquals(1, count($predicates));
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Expression', $predicates[0][1]);
        $this->assertEquals(Predicate::OP_AND, $predicates[0][0]);
        $this->assertEquals('x = ?', $predicates[0][1]->getExpression());

        $where = new Predicate;
        $predicates = $where
                ->addPredicates('x = y')
                ->getPredicates();

        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Literal', $predicates[0][1]);
    }

    public function testMethodsIsMutable()
    {
        $predicate = new Predicate;

        $this->assertSame($predicate, $predicate->equalTo('foo.bar', 'bar'));
        $this->assertSame($predicate, $predicate->notEqualTo('foo.bar', 'bar'));
        $this->assertSame($predicate, $predicate->lessThan('foo.bar', 'bar'));
        $this->assertSame($predicate, $predicate->greaterThan('foo.bar', 'bar'));
        $this->assertSame($predicate, $predicate->lessThanOrEqualTo('foo.bar', 'bar'));
        $this->assertSame($predicate, $predicate->greaterThanOrEqualTo('foo.bar', 'bar'));
        $this->assertSame($predicate, $predicate->like('foo.bar', 'bar%'));
        $this->assertSame($predicate, $predicate->notLike('foo.bar', 'bar%'));
        $this->assertSame($predicate, $predicate->literal('foo.bar = ?', 'bar'));
        $this->assertSame($predicate, $predicate->isNull('foo.bar'));
        $this->assertSame($predicate, $predicate->isNotNull('foo.bar'));
        $this->assertSame($predicate, $predicate->in('foo.bar', ['foo', 'bar']));
        $this->assertSame($predicate, $predicate->notIn('foo.bar', ['foo', 'bar']));
        $this->assertSame($predicate, $predicate->between('foo.bar', 1, 10));
        $this->assertSame($predicate, $predicate->expression('foo = ?', 'bar'));
        $this->assertSame(
            $predicate,
            $predicate
                ->isNull('foo.bar')
                ->or
                ->isNotNull('bar.baz')
                ->and
                ->equalTo('baz.bat', 'foo')
        );
        $this->assertSame(
            $predicate,
            $predicate
                ->isNull('foo.bar')
                ->nest()
                ->isNotNull('bar.baz')
                ->and
                ->equalTo('baz.bat', 'foo')
                ->unnest()
        );
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Predicate::between
     * @covers Zend\Db\Sql\Predicate\Predicate::equalTo
     * @covers Zend\Db\Sql\Predicate\Predicate::expression
     * @covers Zend\Db\Sql\Predicate\Predicate::greaterThan
     * @covers Zend\Db\Sql\Predicate\Predicate::greaterThanOrEqualTo
     * @covers Zend\Db\Sql\Predicate\Predicate::in
     * @covers Zend\Db\Sql\Predicate\Predicate::isNotNull
     * @covers Zend\Db\Sql\Predicate\Predicate::isNull
     * @covers Zend\Db\Sql\Predicate\Predicate::lessThan
     * @covers Zend\Db\Sql\Predicate\Predicate::lessThanOrEqualTo
     * @covers Zend\Db\Sql\Predicate\Predicate::like
     * @covers Zend\Db\Sql\Predicate\Predicate::literal
     * @covers Zend\Db\Sql\Predicate\Predicate::notBetween
     * @covers Zend\Db\Sql\Predicate\Predicate::notEqualTo
     * @covers Zend\Db\Sql\Predicate\Predicate::notIn
     * @covers Zend\Db\Sql\Predicate\Predicate::notLike
     */
    public function testPredicatesIsCorrectInstances()
    {
        $predicate = new Predicate;

        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Between',
            $predicate->between('p1', 'p2', 'p3')->getPredicates()[0][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Operator',
            $predicate->equalTo('p1', 'p2')->getPredicates()[1][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Expression',
            $predicate->expression('', [])->getPredicates()[2][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Operator',
            $predicate->greaterThan('p1', 'p2')->getPredicates()[3][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Operator',
            $predicate->greaterThanOrEqualTo('p1', 'p2')->getPredicates()[4][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\In',
            $predicate->in('p1')->getPredicates()[5][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\IsNotNull',
            $predicate->isNotNull('p1')->getPredicates()[6][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\IsNull',
            $predicate->isNull('p1')->getPredicates()[7][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Operator',
            $predicate->lessThan('p1', 'p2')->getPredicates()[8][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Operator',
            $predicate->lessThanOrEqualTo('p1', 'p2')->getPredicates()[9][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Like',
            $predicate->like('p1', 'p2')->getPredicates()[10][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Literal',
            $predicate->literal('p1')->getPredicates()[11][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\NotBetween',
            $predicate->notBetween('p1', 'p2', 'p3')->getPredicates()[12][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\Operator',
            $predicate->notEqualTo('p1', 'p2')->getPredicates()[13][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\NotIn',
            $predicate->notIn('p1')->getPredicates()[14][1]
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Predicate\NotLike',
            $predicate->notLike('p1', 'p2')->getPredicates()[15][1]
        );
    }
}
