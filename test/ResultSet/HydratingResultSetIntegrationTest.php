<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
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
}
