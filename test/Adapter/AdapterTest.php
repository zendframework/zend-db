<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Adapter;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Profiler;
use Zend\Db\Sql\Select;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockDriver = null;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockPlatform = null;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockConnection = null;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockStatement = null;

    /**
     * @var Adapter
     */
    protected $adapter;

    protected function setUp()
    {
        $this->mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $this->mockConnection = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');
        $this->mockDriver->expects($this->any())->method('checkEnvironment')->will($this->returnValue(true));
        $this->mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($this->mockConnection));
        $this->mockPlatform = $this->getMock('Zend\Db\Adapter\Platform\PlatformInterface');
        $this->mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $this->mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($this->mockStatement));

        $this->adapter = new Adapter($this->mockDriver, $this->mockPlatform);
    }

    /**
     * @testdox unit test: Test setProfiler() will store profiler
     * @covers Zend\Db\Adapter\Adapter::setProfiler
     */
    public function testSetProfiler()
    {
        $ret = $this->adapter->setProfiler(new Profiler\Profiler());
        $this->assertSame($this->adapter, $ret);
    }

    /**
     * @testdox unit test: Test getProfiler() will store profiler
     * @covers Zend\Db\Adapter\Adapter::getProfiler
     */
    public function testGetProfiler()
    {
        $this->adapter->setProfiler($profiler = new Profiler\Profiler());
        $this->assertSame($profiler, $this->adapter->getProfiler());

        $adapter = new Adapter(['driver' => $this->mockDriver, 'profiler' => true], $this->mockPlatform);
        $this->assertInstanceOf('Zend\Db\Adapter\Profiler\Profiler', $adapter->getProfiler());
    }

    /**
     * @testdox unit test: Test createDriverFromParameters() will create proper driver type
     * @covers Zend\Db\Adapter\Adapter::createDriver
     */
    public function testCreateDriver()
    {
        if (extension_loaded('mysqli')) {
            $adapter = new Adapter(['driver' => 'mysqli'], $this->mockPlatform);
            $this->assertInstanceOf('Zend\Db\Adapter\Driver\Mysqli\Mysqli', $adapter->getDriver());
            unset($adapter);
        }

        if (extension_loaded('pgsql')) {
            $adapter = new Adapter(['driver' => 'pgsql'], $this->mockPlatform);
            $this->assertInstanceOf('Zend\Db\Adapter\Driver\Pgsql\Pgsql', $adapter->getDriver());
            unset($adapter);
        }

        if (extension_loaded('sqlsrv')) {
            $adapter = new Adapter(['driver' => 'sqlsrv'], $this->mockPlatform);
            $this->assertInstanceOf('Zend\Db\Adapter\Driver\Sqlsrv\Sqlsrv', $adapter->getDriver());
            unset($adapter);
        }

        if (extension_loaded('pdo')) {
            $adapter = new Adapter(['driver' => 'pdo_sqlite'], $this->mockPlatform);
            $this->assertInstanceOf('Zend\Db\Adapter\Driver\Pdo\Pdo', $adapter->getDriver());
            unset($adapter);
        }
    }

    /**
     * @testdox unit test: Test createPlatformFromDriver() will create proper platform from driver
     * @covers Zend\Db\Adapter\Adapter::createPlatform
     */
    public function testCreatePlatform()
    {
        $driver = clone $this->mockDriver;
        $driver->expects($this->any())->method('getDatabasePlatformName')->will($this->returnValue('Mysql'));
        $adapter = new Adapter($driver);
        $this->assertInstanceOf('Zend\Db\Adapter\Platform\Mysql', $adapter->getPlatform());
        unset($adapter, $driver);

        $driver = clone $this->mockDriver;
        $driver->expects($this->any())->method('getDatabasePlatformName')->will($this->returnValue('SqlServer'));
        $adapter = new Adapter($driver);
        $this->assertInstanceOf('Zend\Db\Adapter\Platform\SqlServer', $adapter->getPlatform());
        unset($adapter, $driver);

        $driver = clone $this->mockDriver;
        $driver->expects($this->any())->method('getDatabasePlatformName')->will($this->returnValue('Postgresql'));
        $adapter = new Adapter($driver);
        $this->assertInstanceOf('Zend\Db\Adapter\Platform\Postgresql', $adapter->getPlatform());
        unset($adapter, $driver);

        $driver = clone $this->mockDriver;
        $driver->expects($this->any())->method('getDatabasePlatformName')->will($this->returnValue('Sqlite'));
        $adapter = new Adapter($driver);
        $this->assertInstanceOf('Zend\Db\Adapter\Platform\Sqlite', $adapter->getPlatform());
        unset($adapter, $driver);

        $driver = clone $this->mockDriver;
        $driver->expects($this->any())->method('getDatabasePlatformName')->will($this->returnValue('IbmDb2'));
        $adapter = new Adapter($driver);
        $this->assertInstanceOf('Zend\Db\Adapter\Platform\IbmDb2', $adapter->getPlatform());
        unset($adapter, $driver);

        $driver = clone $this->mockDriver;
        $driver->expects($this->any())->method('getDatabasePlatformName')->will($this->returnValue('Oracle'));
        $adapter = new Adapter($driver);
        $this->assertInstanceOf('Zend\Db\Adapter\Platform\Oracle', $adapter->getPlatform());
        unset($adapter, $driver);

        $driver = clone $this->mockDriver;
        $driver->expects($this->any())->method('getDatabasePlatformName')->will($this->returnValue('Foo'));
        $adapter = new Adapter($driver);
        $this->assertInstanceOf('Zend\Db\Adapter\Platform\Sql92', $adapter->getPlatform());
        unset($adapter, $driver);

        // ensure platform can created via string, and also that it passed in options to platform object
        $driver = ['driver' => 'pdo_sqlite', 'platform' => 'Oracle', 'platform_options' => ['quote_identifiers' => false]];
        $adapter = new Adapter($driver);
        $this->assertInstanceOf('Zend\Db\Adapter\Platform\Oracle', $adapter->getPlatform());
        $this->assertEquals('foo', $adapter->getPlatform()->quoteIdentifier('foo'));
        unset($adapter, $driver);
    }


    /**
     * @testdox unit test: Test getDriver() will return driver object
     * @covers Zend\Db\Adapter\Adapter::getDriver
     */
    public function testGetDriver()
    {
        $this->assertSame($this->mockDriver, $this->adapter->getDriver());
    }

    /**
     * @testdox unit test: Test getPlatform() returns platform object
     * @covers Zend\Db\Adapter\Adapter::getPlatform
     */
    public function testGetPlatform()
    {
        $this->assertSame($this->mockPlatform, $this->adapter->getPlatform());
    }

    /**
     * @testdox unit test: Test getPlatform() returns platform object
     * @covers Zend\Db\Adapter\Adapter::getQueryResultSetPrototype
     */
    public function testGetQueryResultSetPrototype()
    {
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSetInterface', $this->adapter->getQueryResultSetPrototype());
    }

    /**
     * @testdox unit test: Test getCurrentSchema() returns current schema from connection object
     * @covers Zend\Db\Adapter\Adapter::getCurrentSchema
     */
    public function testGetCurrentSchema()
    {
        $this->mockConnection->expects($this->any())->method('getCurrentSchema')->will($this->returnValue('FooSchema'));
        $this->assertEquals('FooSchema', $this->adapter->getCurrentSchema());
    }

    /**
     * @covers Zend\Db\Adapter\Adapter::getSqlBuilder
     * @covers Zend\Db\Adapter\Adapter::setSqlBuilder
     */
    public function testSetGetSqlBuilder()
    {
        $mockBuilder = $this->getMock('Zend\Db\Adapter\SqlBuilderInterface');

        $this->assertNull($this->adapter->getSqlBuilder());
        $this->assertSame($this->adapter, $this->adapter->setSqlBuilder($mockBuilder));
        $this->assertSame($mockBuilder, $this->adapter->getSqlBuilder());
    }

    /**
     * @covers Zend\Db\Adapter\Adapter::getSqlBuilder
     * @covers Zend\Db\Adapter\Adapter::setSqlBuilder
     */
    public function testSetSqlBuilderViaConstructor()
    {
        $mockBuilder = $this->getMock('Zend\Db\Adapter\SqlBuilderInterface');
        $adapter = new Adapter(
            [
                'driver' => $this->mockDriver,
                'sql_builder' => $mockBuilder,
            ],
            $this->mockPlatform
        );
        $this->assertSame($mockBuilder, $adapter->getSqlBuilder());
    }

    /**
     * @testdox unit test: Test query() in prepare mode produces a statement object
     * @covers Zend\Db\Adapter\Adapter::query
     */
    public function testQueryWhenPreparedProducesStatement()
    {
        $s = $this->adapter->query('SELECT foo');
        $this->assertSame($this->mockStatement, $s);
    }

    /**
     * @testdox unit test: Test query() in prepare mode, with array of parameters, produces a result object
     * @covers Zend\Db\Adapter\Adapter::query
     */
    public function testQueryWhenPreparedWithParameterArrayProducesResult()
    {
        $parray = ['bar'=>'foo'];
        $sql = 'SELECT foo, :bar';
        $statement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $result = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');
        $this->mockDriver->expects($this->any())->method('createStatement')->with($sql)->will($this->returnValue($statement));
        $this->mockStatement->expects($this->any())->method('execute')->will($this->returnValue($result));

        $r = $this->adapter->query($sql, $parray);
        $this->assertSame($result, $r);
    }

    /**
     * @testdox unit test: Test query() in prepare mode, with ParameterContainer, produces a result object
     * @covers Zend\Db\Adapter\Adapter::query
     */
    public function testQueryWhenPreparedWithParameterContainerProducesResult()
    {
        $sql = 'SELECT foo';
        $parameterContainer = $this->getMock('Zend\Db\Adapter\ParameterContainer');
        $result = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');
        $this->mockDriver->expects($this->any())->method('createStatement')->with($sql)->will($this->returnValue($this->mockStatement));
        $this->mockStatement->expects($this->any())->method('execute')->will($this->returnValue($result));
        $result->expects($this->any())->method('isQueryResult')->will($this->returnValue(true));

        $r = $this->adapter->query($sql, $parameterContainer);
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $r);
    }

    /**
     * @testdox unit test: Test query() in execute mode produces a driver result object
     * @covers Zend\Db\Adapter\Adapter::query
     */
    public function testQueryWhenExecutedProducesAResult()
    {
        $sql = 'SELECT foo';
        $result = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');
        $this->mockConnection->expects($this->any())->method('execute')->with($sql)->will($this->returnValue($result));

        $r = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $this->assertSame($result, $r);
    }

    /**
     * @testdox unit test: Test query() in execute mode produces a resultset object
     * @covers Zend\Db\Adapter\Adapter::query
     */
    public function testQueryWhenExecutedProducesAResultSetObjectWhenResultIsQuery()
    {
        $sql = 'SELECT foo';

        $result = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');
        $this->mockConnection->expects($this->any())->method('execute')->with($sql)->will($this->returnValue($result));
        $result->expects($this->any())->method('isQueryResult')->will($this->returnValue(true));

        $r = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $r);

        $r = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE, new TemporaryResultSet());
        $this->assertInstanceOf('ZendTest\Db\Adapter\TemporaryResultSet', $r);
    }

    /**
     * @covers Zend\Db\Adapter\Adapter::query
     */
    public function testQueryWithObjectSql()
    {
        $sql = 'SELECT bar';
        $sqlObject = new Select('bar');

        $resultInterface = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');
        $statementInterface = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');

        $this->mockConnection->expects($this->any())->method('execute')->with($sql)->will($this->returnValue($resultInterface));

        $mockBuilder = $this->getMock('Zend\Db\Adapter\SqlBuilderInterface');
        $mockBuilder->expects($this->any())->method('buildSqlString')->with($sqlObject)->will($this->returnValue($sql));
        $mockBuilder->expects($this->any())->method('prepareSqlStatement')->with($sqlObject)->will($this->returnValue($statementInterface));

        $this->adapter->setSqlBuilder($mockBuilder);

        $this->assertSame(
            $resultInterface,
            $this->adapter->query($sqlObject, Adapter::QUERY_MODE_EXECUTE)
        );
        $this->assertSame(
            $statementInterface,
            $this->adapter->query($sqlObject, Adapter::QUERY_MODE_PREPARE)
        );
    }

    /**
     * @covers Zend\Db\Adapter\Adapter::query
     */
    public function testQueryExecuteObjectSqlWithoutSqlBuilder()
    {
        $this->setExpectedException('Zend\Db\Adapter\Exception\RuntimeException', 'sqlBuilder must be set for non string sql');
        $this->adapter->query(new Select('bar'), Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * @covers Zend\Db\Adapter\Adapter::query
     */
    public function testQueryPrepareObjectSqlWithoutSqlBuilder()
    {
        $this->setExpectedException('Zend\Db\Adapter\Exception\RuntimeException', 'sqlBuilder must be set for non string sql');
        $this->adapter->query(new Select('bar'), Adapter::QUERY_MODE_PREPARE);
    }

    /**
     * @testdox unit test: Test createStatement() produces a statement object
     * @covers Zend\Db\Adapter\Adapter::createStatement
     */
    public function testCreateStatement()
    {
        $this->assertSame($this->mockStatement, $this->adapter->createStatement());
    }
}

class TemporaryResultSet extends \Zend\Db\ResultSet\ResultSet
{
}
