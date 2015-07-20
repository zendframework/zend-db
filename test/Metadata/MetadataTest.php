<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Metadata;

use Zend\Db\Adapter\Platform\AbstractPlatform;
use Zend\Db\Adapter\Platform\Mysql as MysqlPlatform;
use Zend\Db\Adapter\Platform\SqlServer as SqlServerPlatform;
use Zend\Db\Adapter\Platform\Sqlite as SqlitePlatform;
use Zend\Db\Adapter\Platform\Postgresql as PostgresqlPlatform;
use Zend\Db\Adapter\Platform\Oracle as OraclePlatform;
use Zend\Db\Metadata\Metadata;

class MetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createSourceFromAdapterDataProvider
     *
     * @param AbstractPlatform $platform
     * @param string           $sourceExpectation
     */
    public function testTheConstructorSetsTheAdaptersAndTheSource($platform, $sourceExpectation)
    {
        /** @var \Zend\Db\Adapter\Adapter $adapterMock */
        $adapterMock = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
                            ->disableOriginalConstructor()
                            ->getMock();

        $adapterMock->expects($this->once())
                    ->method('getPlatform')
                    ->willReturn($platform);

        $metadata = new Metadata($adapterMock);

        $this->assertAttributeSame($adapterMock, 'adapter', $metadata);
        $this->assertAttributeInstanceOf($sourceExpectation, 'source', $metadata);
    }

    public static function createSourceFromAdapterDataProvider()
    {
        return [
            [new MysqlPlatform(), 'Zend\Db\Metadata\Source\MysqlMetadata'],
            [new SqlServerPlatform(), 'Zend\Db\Metadata\Source\SqlServerMetadata'],
            [new SqlitePlatform(), 'Zend\Db\Metadata\Source\SqliteMetadata'],
            [new PostgresqlPlatform(), 'Zend\Db\Metadata\Source\PostgresqlMetadata'],
            [new OraclePlatform(), 'Zend\Db\Metadata\Source\OracleMetadata'],
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage cannot create source from adapter
     */
    public function testCreateSourceFromAdapterThrowsExceptionIfPlatformIsUnknown()
    {
        $platformMock = $this->getMockForAbstractClass('Zend\Db\Adapter\Platform\AbstractPlatform');

        /** @var \Zend\Db\Adapter\Adapter $adapterMock */
        $adapterMock = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
                            ->disableOriginalConstructor()
                            ->getMock();

        $platformMock->expects($this->once())
                     ->method('getName')
                     ->willReturn('ThisPlatformDoesNotExist');

        $adapterMock->expects($this->once())
                    ->method('getPlatform')
                    ->willReturn($platformMock);

        new Metadata($adapterMock);
    }

    /**
     * @param $method
     * @param $proxyMethod
     * @param $args
     *
     * @dataProvider gettersDataProvider
     */
    public function testGettersProxyToSourceMethods($method, $proxyMethod, $args)
    {
        $returnValue  = rand();

        $platformMock = new MysqlPlatform();

        /** @var \Zend\Db\Adapter\Adapter $adapterMock */
        $adapterMock = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
                            ->disableOriginalConstructor()
                            ->getMock();

        $adapterMock->expects($this->once())
                    ->method('getPlatform')
                    ->willReturn($platformMock);

        $metadata = new Metadata($adapterMock);

        $sourceMock = $this->getMockForAbstractClass(
            'Zend\Db\Metadata\Source\AbstractSource',
            [],
            '',
            false,
            true,
            true,
            [$proxyMethod]
        );

        $sourceMock->expects($this->once())
                   ->method($proxyMethod)
                   ->willReturn($returnValue);

        $metadataReflection = new \ReflectionClass('Zend\Db\Metadata\Metadata');
        $sourceReflectionProperty = $metadataReflection->getProperty('source');
        $sourceReflectionProperty->setAccessible(true);
        $sourceReflectionProperty->setValue($metadata, $sourceMock);

        $this->assertEquals(
            $returnValue,
            call_user_func_array([$metadata, $method], $args)
        );
    }

    public function gettersDataProvider()
    {
        return [
            ['getTables', 'getTables', ['test', false]],
            ['getViews', 'getViews', ['test']],
            ['getTriggers', 'getTriggers', ['test']],
            ['getConstraints', 'getConstraints', [ 'a', 'b']],
            ['getColumns', 'getColumns', ['a', 'b']],
            ['getConstraintKeys', 'getConstraintKeys', ['a', 'b', 'c']],
            ['getConstraint', 'getConstraint', ['a', 'b', 'c']],
            ['getSchemas', 'getSchemas', []],
            ['getTableNames', 'getTableNames', ['a', 'b']],
            ['getTable', 'getTable', ['a', 'b']],
            ['getViewNames', 'getViewNames', ['a']],
            ['getView', 'getView', ['a', 'b']],
            ['getTriggerNames', 'getTriggerNames', ['a']],
            ['getTrigger', 'getTrigger', ['a', 'b']],
            ['getColumnNames', 'getColumnNames', ['a', 'b']],
            ['getColumn', 'getColumn', ['a', 'b', 'c']],
        ];
    }
}
