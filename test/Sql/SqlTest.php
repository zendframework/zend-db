<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql;

use Zend\Db\Sql\Sql;
use ZendTest\Db\TestAsset;
use Zend\Db\Sql\TableIdentifier;

class SqlTest extends \PHPUnit_Framework_TestCase
{
    protected $mockAdapter = null;

    /**
     * Sql object
     * @var Sql
     */
    protected $sql = null;

    public function setup()
    {
        // mock the adapter, driver, and parts
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));

        // setup mock adapter
        $this->mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, [$mockDriver, new TestAsset\TrustingSql92Platform()]);

        $this->sql = new Sql($this->mockAdapter, 'foo');
    }

    /**
     * @covers Zend\Db\Sql\Sql::__construct
     */
    public function test__construct()
    {
        $sql = new Sql($this->mockAdapter);

        $this->assertFalse($sql->hasTable());

        $sql->setTable('foo');
        $this->assertSame('foo', $sql->getTable()->getTable());

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException', 'Table must be a string, array or instance of TableIdentifier.');
        $sql->setTable(null);
    }

    /**
     * @covers Zend\Db\Sql\Sql::setTable
     */
    public function testSetTable()
    {
        $sql = new Sql($this->mockAdapter);

        $this->assertEquals('test', $sql->setTable('test')->getTable()->getTable());
        $this->assertEquals('test', $sql->setTable(new TableIdentifier('test'))->getTable()->getTable());

        $this->setExpectedException(
            'Zend\Db\Sql\Exception\InvalidArgumentException',
            'Table must be a string, array or instance of TableIdentifier.'
        );
        $sql->setTable(null);
    }

    /**
     * @covers Zend\Db\Sql\Sql::select
     */
    public function testSelect()
    {
        $select = $this->sql->select();
        $this->assertInstanceOf('Zend\Db\Sql\Select', $select);
        $this->assertSame('foo', $select->table->getSource()->getTable());

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.');
        $this->sql->select('bar');
    }

    /**
     * @covers Zend\Db\Sql\Sql::insert
     */
    public function testInsert()
    {
        $insert = $this->sql->insert();
        $this->assertInstanceOf('Zend\Db\Sql\Insert', $insert);
        $this->assertSame('foo', $insert->table->getSource()->getTable());

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.');
        $this->sql->insert('bar');
    }

    /**
     * @covers Zend\Db\Sql\Sql::update
     */
    public function testUpdate()
    {
        $update = $this->sql->update();
        $this->assertInstanceOf('Zend\Db\Sql\Update', $update);
        $this->assertSame('foo', $update->table->getSource()->getTable());

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.');
        $this->sql->update('bar');
    }

    /**
     * @covers Zend\Db\Sql\Sql::delete
     */
    public function testDelete()
    {
        $delete = $this->sql->delete();

        $this->assertInstanceOf('Zend\Db\Sql\Delete', $delete);
        $this->assertSame('foo', $delete->table->getSource()->getTable());

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.');
        $this->sql->delete('bar');
    }

    /**
     * @covers Zend\Db\Sql\Sql::getDdl
     */
    public function testGetDdl()
    {
        $ddl = $this->sql->getDdl();
        $this->assertSame($this->sql->getTable(), $ddl->getTable());
        $this->assertSame($this->sql->getAdapter(), $ddl->getAdapter());
        $this->assertSame($this->sql->getBuilder(), $ddl->getBuilder());
    }

    /**
     * @covers Zend\Db\Sql\Sql::prepareSqlStatement
     */
    public function testPrepareSqlStatement()
    {
        $insert = $this->sql->insert()->columns(['foo'])->values(['foo'=>'bar']);
        $stmt = $this->sql->prepareSqlStatement($insert);
        $this->assertInstanceOf('Zend\Db\Adapter\Driver\StatementInterface', $stmt);
    }
}
