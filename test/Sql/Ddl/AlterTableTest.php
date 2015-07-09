<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl;

use Zend\Db\Sql\Ddl\AlterTable;

class AlterTableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Ddl\AlterTable::setTable
     */
    public function testSetTable()
    {
        $at = new AlterTable();
        $this->assertEquals('', $at->table);
        $this->assertSame($at, $at->setTable('test'));
        $this->assertEquals('test', $at->table);
    }

    /**
     * @covers Zend\Db\Sql\Ddl\AlterTable::addColumn
     */
    public function testAddColumn()
    {
        $at = new AlterTable();
        /** @var \Zend\Db\Sql\Ddl\Column\ColumnInterface $colMock */
        $colMock = $this->getMock('Zend\Db\Sql\Ddl\Column\ColumnInterface');
        $this->assertSame($at, $at->addColumn($colMock));
        $this->assertEquals([$colMock], $at->addColumns);
    }

    /**
     * @covers Zend\Db\Sql\Ddl\AlterTable::changeColumn
     */
    public function testChangeColumn()
    {
        $at = new AlterTable();
        /** @var \Zend\Db\Sql\Ddl\Column\ColumnInterface $colMock */
        $colMock = $this->getMock('Zend\Db\Sql\Ddl\Column\ColumnInterface');
        $this->assertSame($at, $at->changeColumn('newname', $colMock));
        $this->assertEquals(['newname' => $colMock], $at->changeColumns);
    }

    /**
     * @covers Zend\Db\Sql\Ddl\AlterTable::dropColumn
     */
    public function testDropColumn()
    {
        $at = new AlterTable();
        $this->assertSame($at, $at->dropColumn('foo'));
        $this->assertEquals(['foo'], $at->dropColumns);
    }

    /**
     * @covers Zend\Db\Sql\Ddl\AlterTable::dropConstraint
     */
    public function testDropConstraint()
    {
        $at = new AlterTable();
        $this->assertSame($at, $at->dropConstraint('foo'));
        $this->assertEquals(['foo'], $at->dropConstraints);
    }

    /**
     * @covers Zend\Db\Sql\Ddl\AlterTable::addConstraint
     */
    public function testAddConstraint()
    {
        $at = new AlterTable();
        /** @var \Zend\Db\Sql\Ddl\Constraint\ConstraintInterface $conMock */
        $conMock = $this->getMock('Zend\Db\Sql\Ddl\Constraint\ConstraintInterface');
        $this->assertSame($at, $at->addConstraint($conMock));
        $this->assertEquals([$conMock], $at->addConstraints);
    }
}
