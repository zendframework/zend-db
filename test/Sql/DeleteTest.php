<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql;

use Zend\Db\Sql\Delete;
use Zend\Db\Sql\TableIdentifier;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Delete
     */
    protected $delete;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->delete = new Delete;
    }

    /**
     * @covers Zend\Db\Sql\Delete::from
     */
    public function testFrom()
    {
        $this->delete->from('foo', 'bar');
        $this->assertEquals('foo', $this->readAttribute($this->delete, 'table'));

        $tableIdentifier = new TableIdentifier('foo', 'bar');
        $this->delete->from($tableIdentifier);
        $this->assertEquals($tableIdentifier, $this->readAttribute($this->delete, 'table'));
    }

    public function test__Get()
    {
        foreach (array_flip($this->readAttribute($this->delete, '__getProperties')) as $name) {
            $this->delete->$name;
        }
        $this->setExpectedException('Zend\Db\Sql\Exception\InvalidArgumentException');
        $this->delete->badPropertyName;
    }
}
