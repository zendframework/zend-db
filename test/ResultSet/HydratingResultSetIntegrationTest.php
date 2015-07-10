<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\ResultSet;

use Zend\Db\ResultSet\HydratingResultSet;

class HydratingResultSetIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\ResultSet\HydratingResultSet::current
     */
    public function testCurrentWillReturnBufferedRow()
    {
        $hydratingRs = new HydratingResultSet;
        $hydratingRs->initialize(new \ArrayIterator([
            ['id' => 1, 'name' => 'one'],
            ['id' => 2, 'name' => 'two'],
        ]));
        $hydratingRs->buffer();
        $obj1 = $hydratingRs->current();
        $hydratingRs->rewind();
        $obj2 = $hydratingRs->current();
        $this->assertSame($obj1, $obj2);
    }

    /**
     * @covers Zend\Db\ResultSet\HydratingResultSet::current
     */
    public function testCurrentUsesPrototypeFactory()
    {
        $dataSource = new \ArrayIterator([
            ['id' => 1, 'name' => 'one'],
            ['id' => 2, 'name' => 'two'],
        ]);

        $factory = $this->getMock('\Zend\Db\ResultSet\HydratingResultSet\PrototypeFactoryInterface');
        $factory->expects($this->once())
            ->method('createPrototype')
            ->with($dataSource[0])
            ->will($this->returnValue(new \ArrayObject));

        $hydratingRs = new HydratingResultSet;
        $hydratingRs->setObjectPrototype($factory);
        $hydratingRs->initialize($dataSource);

        $this->assertInstanceOf('\ArrayObject', $hydratingRs->current());
    }
}
