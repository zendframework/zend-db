<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl;

use Zend\Db\Sql;
use ZendTest\Db\TestAsset;

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
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $this->mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, [$mockDriver, new TestAsset\TrustingSql92Platform()]);

        $this->sql = new Sql\Ddl\Sql($this->mockAdapter, 'foo');
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Sql::alterTable
     */
    public function testAlterTable()
    {
        $alterTable = $this->sql->alterTable();

        $this->assertInstanceOf('Zend\Db\Sql\Ddl\AlterTable', $alterTable);
        $this->assertSame('foo', $alterTable->table->getTable());

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.');
        $this->sql->alterTable('bar');
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Sql::createTable
     */
    public function testCreateTable()
    {
        $createTable = $this->sql->createTable();

        $this->assertInstanceOf('Zend\Db\Sql\Ddl\CreateTable', $createTable);
        $this->assertSame('foo', $createTable->table->getTable());

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.');
        $this->sql->createTable('bar');
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Sql::dropTable
     */
    public function testDropTable()
    {
        $dropTable = $this->sql->dropTable();

        $this->assertInstanceOf('Zend\Db\Sql\Ddl\DropTable', $dropTable);
        $this->assertSame('foo', $dropTable->table->getTable());

        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.');
        $this->sql->dropTable('bar');
    }
}
