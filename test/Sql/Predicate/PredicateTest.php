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
     * moved from SelectTest
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
     * moved from SelectTest
     */
    public function testWhereArgument1IsAssociativeArrayNotContainingReplacementCharacter()
    {
        $where = new Predicate;
        $where->addPredicates(['name' => 'Ralph', 'age' => 33]);
        $predicates = $where->getPredicates();
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
     * moved from SelectTest
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
     * moved from SelectTest
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
     * moved from SelectTest
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
     * moved from SelectTest
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
     * moved from SelectTest
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
}
