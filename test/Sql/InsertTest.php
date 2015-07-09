<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\TableIdentifier;

class InsertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Insert
     */
    protected $insert;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->insert = new Insert;
    }

    /**
     * @covers Zend\Db\Sql\Insert::into
     */
    public function testInto()
    {
        $this->insert->into('table', 'schema');
        $this->assertEquals('table', $this->insert->table);

        $tableIdentifier = new TableIdentifier('table', 'schema');
        $this->insert->into($tableIdentifier);
        $this->assertEquals($tableIdentifier, $this->insert->table);
    }

    /**
     * @covers Zend\Db\Sql\Insert::columns
     */
    public function testColumns()
    {
        $this->insert->columns(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $this->insert->columns);
    }

    /**
     * @covers Zend\Db\Sql\Insert::values
     */
    public function testValues()
    {
        $this->insert->values(['foo' => 'bar']);
        $this->assertEquals(['foo'], $this->insert->columns);
        $this->assertEquals(['bar'], $this->insert->values);

        // test will merge cols and values of previously set stuff
        $this->insert->values(['foo' => 'bax'], Insert::VALUES_MERGE);
        $this->insert->values(['boom' => 'bam'], Insert::VALUES_MERGE);
        $this->assertEquals(['foo', 'boom'], $this->insert->columns);
        $this->assertEquals(['bax', 'bam'], $this->insert->values);

        $this->insert->values(['foo' => 'bax']);
        $this->assertEquals(['foo'], $this->insert->columns);
        $this->assertEquals(['bax'], $this->insert->values);
    }

    /**
     * @covers Zend\Db\Sql\Insert::values
     */
    public function testValuesThrowsExceptionWhenNotArrayOrSelect()
    {
        $this->setExpectedException(
            'Zend\Db\Sql\Exception\InvalidArgumentException',
            'values() expects an array of values or Zend\Db\Sql\SelectableInterface instance'
        );
        $this->insert->values(5);
    }

    /**
     * @covers Zend\Db\Sql\Insert::values
     */
    public function testValuesThrowsExceptionWhenSelectMergeOverArray()
    {
        $this->insert->values(['foo' => 'bar']);

        $this->setExpectedException(
            'Zend\Db\Sql\Exception\InvalidArgumentException',
            'A Zend\Db\Sql\SelectableInterface instance cannot be provided with the merge flag'
        );
        $this->insert->values(new Select, Insert::VALUES_MERGE);
    }

    /**
     * @covers Zend\Db\Sql\Insert::values
     */
    public function testValuesThrowsExceptionWhenArrayMergeOverSelect()
    {
        $this->insert->values(new Select);

        $this->setExpectedException(
            'Zend\Db\Sql\Exception\InvalidArgumentException',
            'An array of values cannot be provided with the merge flag when a Zend\Db\Sql\SelectableInterface instance already exists as the value source'
        );
        $this->insert->values(['foo' => 'bar'], Insert::VALUES_MERGE);
    }

    /**
     * @covers Zend\Db\Sql\Insert::values
     * @group ZF2-4926
     */
    public function testEmptyArrayValues()
    {
        $this->insert->values([]);
        $this->assertEquals([], $this->readAttribute($this->insert, 'columns'));
    }
}
