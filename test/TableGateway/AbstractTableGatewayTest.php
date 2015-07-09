<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\TableGateway;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\ResultSet\ResultSet;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-03-01 at 21:02:22.
 */
class AbstractTableGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_Generator
     */
    protected $mockAdapter = null;

    /**
     * @var \Zend\Db\Adapter\Driver\StatementInterface
     */
    protected $mockStatement;

    /**
     * @var \PHPUnit_Framework_MockObject_Generator
     */
    protected $mockSql = null;

    /**
     * @var AbstractTableGateway
     */
    protected $table;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        // mock the adapter, driver, and parts
        $mockResult = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');
        $mockResult->expects($this->any())->method('getAffectedRows')->will($this->returnValue(5));

        $this->mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $stmtSQL = function ($sql = null) {
            static $data;
            if ($sql === null) {
                return $data;
            }
            $data = $sql;
        };

        $this->mockStatement->expects($this->any())->method('setSql')->will($this->returnCallback($stmtSQL));
        $this->mockStatement->expects($this->any())->method('getSql')->will($this->returnCallback($stmtSQL));
        $this->mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $this->mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue(new ParameterContainer));

        $mockConnection = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');
        $mockConnection->expects($this->any())->method('getLastGeneratedValue')->will($this->returnValue(10));

        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($this->mockStatement));
        $mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));

        $this->mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, [$mockDriver]);
        $this->mockSql = new \Zend\Db\Sql\Sql($this->mockAdapter, 'foo');

        $this->table = $this->getMockForAbstractClass(
            'Zend\Db\TableGateway\AbstractTableGateway'
            //array('getTable')
        );
        $tgReflection = new \ReflectionClass('Zend\Db\TableGateway\AbstractTableGateway');
        foreach ($tgReflection->getProperties() as $tgPropReflection) {
            $tgPropReflection->setAccessible(true);
            switch ($tgPropReflection->getName()) {
                case 'table':
                    $tgPropReflection->setValue($this->table, 'foo');
                    break;
                case 'adapter':
                    $tgPropReflection->setValue($this->table, $this->mockAdapter);
                    break;
                case 'resultSetPrototype':
                    $tgPropReflection->setValue($this->table, new ResultSet);
                    break;
                case 'sql':
                    $tgPropReflection->setValue($this->table, $this->mockSql);
                    break;
            }
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::getTable
     */
    public function testGetTable()
    {
        $this->assertEquals('foo', $this->table->getTable());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::getAdapter
     */
    public function testGetAdapter()
    {
        $this->assertSame($this->mockAdapter, $this->table->getAdapter());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::getSql
     */
    public function testGetSql()
    {
        $this->assertInstanceOf('Zend\Db\Sql\Sql', $this->table->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::getResultSetPrototype
     */
    public function testGetSelectResultPrototype()
    {
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $this->table->getResultSetPrototype());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::select
     * @covers Zend\Db\TableGateway\AbstractTableGateway::selectWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeSelect
     */
    public function testSelectWithNoWhere()
    {
        $resultSet = $this->table->select();

        // check return types
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $resultSet);
        $this->assertNotSame($this->table->getResultSetPrototype(), $resultSet);
        $this->assertEquals('SELECT "foo".* FROM "foo"', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::select
     * @covers Zend\Db\TableGateway\AbstractTableGateway::selectWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeSelect
     */
    public function testSelectWithWhereString()
    {
        $resultSet = $this->table->select('whereCondition');
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $resultSet);
        $this->assertEquals('SELECT "foo".* FROM "foo" WHERE whereCondition', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::select
     * @covers Zend\Db\TableGateway\AbstractTableGateway::selectWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeSelect
     *
     * This is a test for the case when a valid $select is built using an aliased table name, then used
     * with AbstractTableGateway::selectWith (or AbstractTableGateway::select).
     *
     * $myTable = new MyTable(...);
     * $sql = new \Zend\Db\Sql\Sql(...);
     * $select = $sql->select()->from(array('t' => 'mytable'));
     *
     * // Following fails, with Fatal error: Uncaught exception 'RuntimeException' with message
     * 'The table name of the provided select object must match that of the table' unless fix is provided.
     * $myTable->selectWith($select);
     *
     */
    public function testSelectWithArrayTable()
    {
        // Case 1

        $select1 = new \Zend\Db\Sql\Select('bat');

        $return = $this->table->selectWith($select1);
        $this->assertNotNull($return);
        $this->assertEquals('SELECT "bat".* FROM "bat"', $this->mockStatement->getSql());

        // Case 2

        $select1 = new \Zend\Db\Sql\Select(['f' => 'foo']);
        $return = $this->table->selectWith($select1);
        $this->assertNotNull($return);
        $this->assertEquals('SELECT "f".* FROM "foo" AS "f"', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::insert
     * @covers Zend\Db\TableGateway\AbstractTableGateway::insertWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeInsert
     */
    public function testInsert()
    {
        $affectedRows = $this->table->insert(['foo' => 'bar']);
        $this->assertEquals(5, $affectedRows);
        $this->assertEquals('INSERT INTO "foo" ("foo") VALUES (?)', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::update
     * @covers Zend\Db\TableGateway\AbstractTableGateway::updateWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeUpdate
     */
    public function testUpdate()
    {
        $affectedRows = $this->table->update(['foo' => 'bar'], 'id = 2');
        $this->assertEquals(5, $affectedRows);
        $this->assertEquals('UPDATE "foo" SET "foo" = ? WHERE id = 2', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::update
     * @covers Zend\Db\TableGateway\AbstractTableGateway::updateWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeUpdate
     */
    public function testUpdateWithJoin()
    {
        $joins = [
            [
                'name' => 'baz',
                'on'   => 'foo.fooId = baz.fooId',
                'type' => Sql\Join::JOIN_LEFT
            ]
        ];
        $affectedRows = $this->table->update(['foo.field' => 'bar'], 'id = 2', $joins);
        $this->assertEquals(5, $affectedRows);
        $this->assertEquals('UPDATE "foo" LEFT JOIN "baz" ON "foo"."fooId" = "baz"."fooId" SET "foo.field" = ? WHERE id = 2', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::update
     * @covers Zend\Db\TableGateway\AbstractTableGateway::updateWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeUpdate
     */
    public function testUpdateWithJoinDefaultType()
    {
        $affectedRows = $this->table->update(
            ['foo.field' => 'bar'],
            'id = 2',
            [[
                'name' => 'baz',
                'on'   => 'foo.fooId = baz.fooId',
            ]]
        );
        $this->assertEquals(5, $affectedRows);
        $this->assertEquals('UPDATE "foo" INNER JOIN "baz" ON "foo"."fooId" = "baz"."fooId" SET "foo.field" = ? WHERE id = 2', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::update
     * @covers Zend\Db\TableGateway\AbstractTableGateway::updateWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeUpdate
     */
    public function testUpdateWithNoCriteria()
    {
        $affectedRows = $this->table->update(['foo' => 'bar']);
        $this->assertEquals(5, $affectedRows);
        $this->assertEquals('UPDATE "foo" SET "foo" = ?', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::delete
     * @covers Zend\Db\TableGateway\AbstractTableGateway::deleteWith
     * @covers Zend\Db\TableGateway\AbstractTableGateway::executeDelete
     */
    public function testDelete()
    {
        $affectedRows = $this->table->delete('whereCondition');
        $this->assertEquals(5, $affectedRows);
        $this->assertEquals('DELETE FROM "foo" WHERE whereCondition', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::getLastInsertValue
     */
    public function testGetLastInsertValue()
    {
        $this->table->insert(['foo' => 'bar']);
        $this->assertEquals(10, $this->table->getLastInsertValue());
        $this->assertEquals('INSERT INTO "foo" ("foo") VALUES (?)', $this->mockStatement->getSql());
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::__get
     */
    public function test__get()
    {
        $affectedRows = $this->table->insert(['foo' => 'bar']); // trigger last insert id update
        $this->assertEquals('INSERT INTO "foo" ("foo") VALUES (?)', $this->mockStatement->getSql());
        $this->assertEquals(5, $affectedRows);
        $this->assertEquals(10, $this->table->lastInsertValue);
        $this->assertSame($this->mockAdapter, $this->table->adapter);
    }

    /**
     * @covers Zend\Db\TableGateway\AbstractTableGateway::__clone
     */
    public function test__clone()
    {
        $cTable = clone $this->table;
        $this->assertSame($this->mockAdapter, $cTable->getAdapter());
    }
}
