<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\Builder\Builder;
use Zend\Db\Sql;

class BuilderTest extends AbstractTestCase
{
    /**
     * @var Builder
     */
    protected $builder;

    public function setUp()
    {
        $this->builder = new Builder();
        $inheritableBuilders = new \ReflectionProperty($this->builder, 'inheritableBuilders');
        $inheritableBuilders->setAccessible(true);
        $inheritableBuilders->setValue($this->builder, [
            'Zend\Db\Sql\Select'          => [
                'sql92'     => 'Zend\Db\Sql\Builder\sql92\SelectBuilder',
                'mysql'     => 'Zend\Db\Sql\Builder\MySql\SelectBuilder',
            ],
            'Zend\Db\Sql\Ddl\CreateTable' => [
                'sqlserver' => 'Zend\Db\Sql\Builder\SqlServer\Ddl\CreateTableBuilder',
            ],
        ]);
    }

    /**
     * @expectedException Zend\Db\Sql\Exception\RuntimeException
     */
    public function testGePlatformBuilderForNotExistsObject()
    {
        $this->builder->getPlatformBuilder(new Sql\Insert());
    }

    /**
     * @covers Zend\Db\Sql\Builder\Builder::getPlatformBuilder
     * @expectedException Zend\Db\Sql\Exception\RuntimeException
     */
    public function testGePlatformBuilderForNotExistsPlatform()
    {
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\sql92\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select(), 'NotExistingPlatform')
        );
        $this->builder->getPlatformBuilder(new Sql\Ddl\CreateTable(), 'NotExistingPlatform');
    }

    /**
     * @covers Zend\Db\Sql\Builder\Builder::getPlatformBuilder
     */
    public function testGetPlatformBuilder()
    {
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\sql92\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select())
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\sql92\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select(), 'sql92')
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\MySql\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select(), 'mysql')
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\sql92\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select(), 'sqlserver')
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\SqlServer\Ddl\CreateTableBuilder',
            $this->builder->getPlatformBuilder(new Sql\Ddl\CreateTable(), 'sqlserver')
        );
    }

    /**
     * @covers Zend\Db\Sql\Builder\Builder::setPlatformBuilder
     */
    public function testSetPlatformBuilder()
    {
        $this->builder->setPlatformBuilder('ibmdb2', 'Zend\Db\Sql\Select', 'Zend\Db\Sql\Builder\IbmDb2\SelectBuilder');
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\sql92\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select(), 'sql92')
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\IbmDb2\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select(), 'ibmdb2')
        );

        $oracleSelectBuilder = new \Zend\Db\Sql\Builder\Oracle\SelectBuilder($this->builder);
        $this->builder->setPlatformBuilder('oracle', 'Zend\Db\Sql\Select', $oracleSelectBuilder);
        $this->assertSame(
            $oracleSelectBuilder,
            $this->builder->getPlatformBuilder(new Sql\Select(), 'oracle')
        );
    }

    /**
     * @covers Zend\Db\Sql\Builder\Builder::setPlatformBuilders
     */
    public function testSetPlatformBuilders()
    {
        $oracleSelectBuilder = new \Zend\Db\Sql\Builder\Oracle\SelectBuilder($this->builder);
        $this->builder->setPlatformBuilders([
            'ibmdb2' => [
                'Zend\Db\Sql\Select' => 'Zend\Db\Sql\Builder\IbmDb2\SelectBuilder',
            ],
            'oracle' => [
                'Zend\Db\Sql\Select' => $oracleSelectBuilder,
            ],
        ]);

        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\sql92\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select(), 'sql92')
        );
        $this->assertInstanceOf(
            'Zend\Db\Sql\Builder\IbmDb2\SelectBuilder',
            $this->builder->getPlatformBuilder(new Sql\Select(), 'ibmdb2')
        );
        $this->assertSame(
            $oracleSelectBuilder,
            $this->builder->getPlatformBuilder(new Sql\Select(), 'oracle')
        );
    }

    /**
     * @expectedException Zend\Db\Sql\Exception\InvalidArgumentException
     */
    public function testSetWrongPlatformBuilder()
    {
        $this->builder->setPlatformBuilder('oracle', 'Zend\Db\Sql\Select', new \stdClass());
    }

    /**
     * @covers Zend\Db\Sql\Builder\Builder::setDefaultAdapter
     * @covers Zend\Db\Sql\Builder\Builder::getDefaultAdapter
     */
    public function testDefaultAdapter()
    {
        $builder = new Builder();
        $this->assertNull($builder->getDefaultAdapter());

        $adapter = $this->getAdapterForPlatform('sqlserver');
        $builder = new Builder($adapter);
        $this->assertSame($adapter, $builder->getDefaultAdapter());

        $adapter = $this->getAdapterForPlatform('sql92');
        $this->assertSame($adapter, $builder->setDefaultAdapter($adapter)->getDefaultAdapter());

        $adapter = $this->getAdapterForPlatform('mysql');
        $this->assertSame($adapter, $builder->setDefaultAdapter($adapter)->getDefaultAdapter());
    }

    /**
     * @covers Zend\Db\Sql\Builder\Builder::buildSqlString
     */
    public function testBuildSqlString()
    {
        $this->assertInternalType(
            'string',
            $this->builder->buildSqlString(
                new Sql\Select('foo'),
                $this->getAdapterForPlatform('sql92')
            )
        );
    }

    /**
     * @covers Zend\Db\Sql\Builder\Builder::prepareSqlStatement
     */
    public function testPrepareSqlStatement()
    {
        $statement = $this->builder->prepareSqlStatement(
            new Sql\Select('foo'),
            $this->getAdapterForPlatform('sql92')
        );
        $this->assertInstanceOf('Zend\Db\Adapter\Driver\StatementInterface', $statement);
        $this->assertInstanceOf('Zend\Db\Adapter\ParameterContainer', $statement->getParameterContainer());
    }
}
