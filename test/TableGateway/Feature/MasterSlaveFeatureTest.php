<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\TableGateway\Feature;

use Zend\Db\TableGateway\Feature\MasterSlaveFeature;

class MasterSlaveFeatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $mockMasterAdapter, $mockSlaveAdapter;

    /**
     * @var MasterSlaveFeature
     */
    protected $feature;

    /** @var \Zend\Db\TableGateway\TableGateway */
    protected $table;

    public function setup()
    {
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue(
            $mockStatement
        ));
        $this->mockMasterAdapter = new \Zend\Db\Adapter\Adapter($mockDriver, new \Zend\Db\Adapter\Platform\Sql92());

        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue(
            $mockStatement
        ));
        $this->mockSlaveAdapter = new \Zend\Db\Adapter\Adapter($mockDriver, new \Zend\Db\Adapter\Platform\Sql92());

        $this->feature = new MasterSlaveFeature($this->mockSlaveAdapter);
    }

    public function testPostInitialize()
    {
        /** @var $table \Zend\Db\TableGateway\TableGateway */
        $this->getMockForAbstractClass(
            'Zend\Db\TableGateway\TableGateway',
            ['foo', $this->mockMasterAdapter, $this->feature]
        );
        // postInitialize is run
        $this->assertSame($this->mockSlaveAdapter, $this->feature->getSlaveSql()->getAdapter());
    }

    public function testPreSelect()
    {
        $table = $this->getMockForAbstractClass(
            'Zend\Db\TableGateway\TableGateway',
            ['foo', $this->mockMasterAdapter, $this->feature]
        );

        $this->mockSlaveAdapter->getDriver()->createStatement()
            ->expects($this->once())->method('execute')->will($this->returnValue(
                $this->getMock('Zend\Db\ResultSet\ResultSet')
            ));
        $table->select('foo = bar');
    }

    public function testPostSelect()
    {
        $table = $this->getMockForAbstractClass(
            'Zend\Db\TableGateway\TableGateway',
            ['foo', $this->mockMasterAdapter, $this->feature]
        );
        $this->mockSlaveAdapter->getDriver()->createStatement()
            ->expects($this->once())->method('execute')->will($this->returnValue(
            $this->getMock('Zend\Db\ResultSet\ResultSet')
        ));

        $masterSql = $table->getSql();
        $table->select('foo = bar');

        // test that the sql object is restored
        $this->assertSame($masterSql, $table->getSql());
    }
}
