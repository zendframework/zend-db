<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;
use Zend\Stdlib\ArrayUtils;
use Zend\Db;
use Zend\Db\Sql\Builder\Builder;

class BuilderServiceFactoryTest extends TestCase
{
    public function getBuilder($config = [])
    {
        $config = ArrayUtils::merge(
            (new Db\ConfigProvider)->getDependencyConfig(),
            ArrayUtils::merge([
                'services' => [
                    'Config' => [],
                ],
            ], $config)
        );
        $serviceManager = new ServiceManager();
        (new Config($config))->configureServiceManager($serviceManager);
        return $serviceManager->get('SqlBuilder');
    }

    public function testFactoryWithEmptyConfig()
    {
        $builder = $this->getBuilder();

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertNull($builder->getDefaultAdapter());
        $this->assertInstanceOf(
            Db\Sql\Builder\sql92\SelectBuilder::class,
            $builder->getPlatformBuilder(new Db\Sql\Select)
        );
    }

    public function testFactoryWithoutAdapterConfig()
    {
        $builder = $this->getBuilder(['services' => ['Config' => [
            'sql_builder' => [
                'builders' => [
                    'sql92' => [
                        Db\Sql\Select::class => Db\Sql\Builder\MySql\SelectBuilder::class,
                    ],
                ],
            ],
        ]]]);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertNull($builder->getDefaultAdapter());
        $this->assertInstanceOf(
            Db\Sql\Builder\MySql\SelectBuilder::class,
            $builder->getPlatformBuilder(new Db\Sql\Select)
        );
    }

    public function testFactoryWithFullConfig()
    {
        $builder = $this->getBuilder(['services' => ['Config' => [
            'db' => [
                'driver' => 'mysqli',
            ],
            'sql_builder' => [
                'default_adapter' => Db\Adapter\Adapter::class,
                'builders' => [
                    'sql92' => [
                        Db\Sql\Select::class => Db\Sql\Builder\MySql\SelectBuilder::class,
                    ],
                ],
            ],
        ]]]);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertInstanceOf(
            Db\Adapter\Driver\Mysqli\Mysqli::class,
            $builder->getDefaultAdapter()->getDriver()
        );
        $this->assertInstanceOf(
            Db\Sql\Builder\Mysql\SelectBuilder::class,
            $builder->getPlatformBuilder(new Db\Sql\Select)
        );
    }
}
