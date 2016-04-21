<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\TableSource;
use Zend\Db\Sql\Joins;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Select::__construct
     */
    public function testConstruct()
    {
        $select = new Select('foo');
        $this->assertEquals('foo', $select->table->getSource()->getTable());
    }

    public function testMethodsReturnSelf()
    {
        $select = new Select;
        $this->assertSame($select, $select->from('foo', 'bar'));
        $this->assertSame($select, $select->quantifier($select::QUANTIFIER_DISTINCT));
        $this->assertSame($select, $select->columns(['foo', 'bar']));
        $this->assertSame($select, $select->join('foo', 'x = y', Select::SQL_STAR, Joins::JOIN_INNER));
        $this->assertSame($select, $select->where('x = y'));
        $this->assertSame($select, $select->limit(5));
        $this->assertSame($select, $select->offset(10));
        $this->assertSame($select, $select->group(['col1', 'col2']));
        $this->assertSame($select, $select->having(['x = ?' => 5]));
        return $select;
    }

    /**
     * @testdox unit test: Test __get() returns expected objects magically
     * @covers Zend\Db\Sql\Select::__get
     * @depends testMethodsReturnSelf
     */
    public function testMagicAccessor(Select $select)
    {
        $this->assertInstanceOf('Zend\Db\Sql\Where', $select->where);
        $this->assertEquals(['foo', 'bar'], $select->columns);
        $this->assertEquals('foo', $select->table->getSource()->getTable());
        $this->assertEquals(['col1', 'col2'], $select->group);
        $this->assertEquals(
            [[
                'name' => new TableSource(new TableIdentifier('foo')),
                'on' => 'x = y',
                'columns' => [Select::SQL_STAR],
                'type' => Joins::JOIN_INNER
            ]],
            $select->joins->getJoins()
        );
        $this->assertInstanceOf('Zend\Db\Sql\Having', $select->having);
        $this->assertEquals(5, $select->limit);
        $this->assertEquals(10, $select->offset);
        $this->assertEquals(Select::QUANTIFIER_DISTINCT, $select->quantifier);
    }

    /**
     * @testdox unit test: Test quantifier() accepts expression
     * @covers Zend\Db\Sql\Select::quantifier
     */
    public function testQuantifierParameterExpressionInterface()
    {
        $expr = $this->getMock('Zend\Db\Sql\ExpressionInterface');
        $select = new Select;
        $select->quantifier($expr);
        $this->assertSame(
            $expr,
            $select->quantifier
        );
    }

    /**
     * @testdox unit test: Test where() will accept any Predicate object as-is
     * @covers Zend\Db\Sql\Select::where
     */
    public function testWhereArgument1IsPredicate()
    {
        $select = new Select;
        $predicate = new Predicate\Predicate([
            new Predicate\Expression('name = ?', 'Ralph'),
            new Predicate\Expression('age = ?', 33),
        ]);
        $select->where($predicate);

        $predicates = $select->where->getPredicates();
        $this->assertSame($predicate, $predicates[0][1]);
    }
    /**
     * @testdox unit test: Test where() will accept a Where object
     * @covers Zend\Db\Sql\Select::where
     */
    public function testWhereArgument1IsWhereObject()
    {
        $select = new Select;
        $select->where($newWhere = new Where);
        $this->assertSame($newWhere, $select->where);
    }

    /**
     * @author Rob Allen
     * @testdox unit test: Test order()
     * @covers Zend\Db\Sql\Select::order
     */
    public function testOrder()
    {
        $select = new Select;
        $return = $select->order('id DESC');
        $this->assertSame($select, $return); // test fluent interface
        $this->assertEquals(['id DESC'], $select->order);

        $select = new Select;
        $select->order('id DESC')
            ->order('name ASC, age DESC');
        $this->assertEquals(['id DESC', 'name ASC', 'age DESC'], $select->order);

        $select = new Select;
        $select->order(['name ASC', 'age DESC']);
        $this->assertEquals(['name ASC', 'age DESC'], $select->order);
    }
    /**
     * @testdox: unit test: test limit() throws execption when invalid parameter passed
     * @covers Zend\Db\Sql\Select::limit
     */
    public function testLimitExceptionOnInvalidParameter()
    {
        $select = new Select;
        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException', 'Zend\Db\Sql\Select::limit expects parameter to be numeric');
        $select->limit('foobar');
    }

    /**
     * @testdox: unit test: test offset() throws exception when invalid parameter passed
     * @covers Zend\Db\Sql\Select::offset
     */
    public function testOffsetExceptionOnInvalidParameter()
    {
        $select = new Select;
        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException', 'Zend\Db\Sql\Select::offset expects parameter to be numeric');
        $select->offset('foobar');
    }

    /**
     * @testdox unit test: Test having() returns same Select object (is chainable)
     * @covers Zend\Db\Sql\Select::having
     */
    public function testHavingArgument1IsHavingObject()
    {
        $select = new Select;
        $having = new Having();
        $return = $select->having($having);
        $this->assertSame($select, $return);
        $this->assertSame($having, $select->having);

        return $return;
    }

    /**
     * @testdox unit test: Test reset() resets internal stat of Select object, based on input
     * @covers Zend\Db\Sql\Select::__unset
     */
    public function testMagicUnset()
    {
        $select = new Select;

        $originalValues = [];
        $clonedSelect = clone $select;
        foreach (array_flip($this->readAttribute($clonedSelect, '__getProperties')) as $name) {
            $originalValues[$name] = $clonedSelect->$name;
        }

        $select
            ->from('foo', 'bar')
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['foo', 'bar'])
            ->setPrefixColumnsWithTable(false)
            ->join('foo', 'x = y', Select::SQL_STAR, Joins::JOIN_INNER)
            ->where('x = y')
            ->limit(5)
            ->offset(10)
            ->group(['col1', 'col2'])
            ->having(['x = ?' => 5])
            ->order(Select::ORDER_ASCENDING)
            ->combine(new Select('xxx'));

        foreach ($originalValues as $name => $value) {
            $this->assertNotEquals($value, $select->$name, 'notEqual : ' . $name);
            unset($select->$name);
            $this->assertEquals($value, $select->$name, 'Equal : ' . $name);
        }
    }

    /**
     * @testdox unit test: Test __clone() will clone the where object so that this select can be used in multiple contexts
     * @covers Zend\Db\Sql\Select::__clone
     */
    public function testCloning()
    {
        $select = new Select;
        $select1 = clone $select;
        $select1->where('id = foo');
        $select1->having('id = foo');

        $this->assertEquals(0, $select->where->count());
        $this->assertEquals(1, $select1->where->count());

        $this->assertEquals(0, $select->having->count());
        $this->assertEquals(1, $select1->having->count());
    }
}
