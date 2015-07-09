<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql;

use Zend\Db\Sql\Join;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\TableIdentifier;

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Update
     */
    protected $update;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->update = new Update;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Zend\Db\Sql\Update::table
     */
    public function testTable()
    {
        $this->update->table('foo', 'bar');
        $this->assertEquals('foo', $this->readAttribute($this->update, 'table'));

        $tableIdentifier = new TableIdentifier('foo', 'bar');
        $this->update->table($tableIdentifier);
        $this->assertEquals($tableIdentifier, $this->readAttribute($this->update, 'table'));
    }

    /**
     * @covers Zend\Db\Sql\Update::__construct
     */
    public function testConstruct()
    {
        $update = new Update('foo');
        $this->assertEquals('foo', $this->readAttribute($update, 'table'));
    }

    /**
     * @covers Zend\Db\Sql\Update::set
     */
    public function testSet()
    {
        $this->update->set(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $this->update->set->toArray());
    }

    /**
     * @covers Zend\Db\Sql\Update::set
     */
    public function testSortableSet()
    {
        $this->update->set([
            'two'   => 'с_two',
            'three' => 'с_three',
        ]);
        $this->update->set(['one' => 'с_one'], 10);

        $this->assertEquals(
            [
                'one'   => 'с_one',
                'two'   => 'с_two',
                'three' => 'с_three',
            ],
            $this->update->set->toArray()
        );
    }

    /**
     * @covers Zend\Db\Sql\Update::__get
     */
    public function test__Get()
    {
        $this->update->table('foo')
            ->set(['bar' => 'baz'])
            ->where('x = y');

        $this->assertEquals('foo', $this->update->table);
        $this->assertEquals(['bar' => 'baz'], $this->update->set->toArray());
        $this->assertInstanceOf('Zend\Db\Sql\Where', $this->update->where);
    }


    /**
     * @covers Zend\Db\Sql\Update::__get
     */
    public function testGetUpdate()
    {
        $getWhere = $this->update->__get('where');
        $this->assertInstanceOf('Zend\Db\Sql\Where', $getWhere);
    }

    /**
     * @covers Zend\Db\Sql\Update::__get
     */
    public function testGetUpdateFails()
    {
        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException');
        $getWhat = $this->update->__get('what');
        $this->assertNull($getWhat);
    }

    /**
     * @testdox unit test: Test join() returns Update object (is chainable)
     * @covers Zend\Db\Sql\Update::join
     */
    public function testJoinChainable()
    {
        $return = $this->update->join('baz', 'foo.fooId = baz.fooId', Join::JOIN_LEFT);
        $this->assertSame($this->update, $return);
    }
}
