<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl\Constraint;

use Zend\Db\Sql\Ddl\Constraint\ForeignKey;

class ForeignKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::setName
     */
    public function testSetName()
    {
        $fk = new ForeignKey('foo', 'bar', 'baz', 'bam');
        $this->assertSame($fk, $fk->setName('xxxx'));
        return $fk;
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::getName
     * @depends testSetName
     */
    public function testGetName(ForeignKey $fk)
    {
        $this->assertEquals('xxxx', $fk->getName());
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::setReferenceTable
     */
    public function testSetReferenceTable()
    {
        $fk = new ForeignKey('foo', 'bar', 'baz', 'bam');
        $this->assertSame($fk, $fk->setReferenceTable('xxxx'));
        return $fk;
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::getReferenceTable
     * @depends testSetReferenceTable
     */
    public function testGetReferenceTable(ForeignKey $fk)
    {
        $this->assertEquals('xxxx', $fk->getReferenceTable());
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::setReferenceColumn
     */
    public function testSetReferenceColumn()
    {
        $fk = new ForeignKey('foo', 'bar', 'baz', 'bam');
        $this->assertSame($fk, $fk->setReferenceColumn('xxxx'));
        return $fk;
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::getReferenceColumn
     * @depends testSetReferenceColumn
     */
    public function testGetReferenceColumn(ForeignKey $fk)
    {
        $this->assertEquals(['xxxx'], $fk->getReferenceColumn());
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::setOnDeleteRule
     */
    public function testSetOnDeleteRule()
    {
        $fk = new ForeignKey('foo', 'bar', 'baz', 'bam');
        $this->assertSame($fk, $fk->setOnDeleteRule('CASCADE'));
        return $fk;
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::getOnDeleteRule
     * @depends testSetOnDeleteRule
     */
    public function testGetOnDeleteRule(ForeignKey $fk)
    {
        $this->assertEquals('CASCADE', $fk->getOnDeleteRule());
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::setOnUpdateRule
     */
    public function testSetOnUpdateRule()
    {
        $fk = new ForeignKey('foo', 'bar', 'baz', 'bam');
        $this->assertSame($fk, $fk->setOnUpdateRule('CASCADE'));
        return $fk;
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\ForeignKey::getOnUpdateRule
     * @depends testSetOnUpdateRule
     */
    public function testGetOnUpdateRule(ForeignKey $fk)
    {
        $this->assertEquals('CASCADE', $fk->getOnUpdateRule());
    }
}
