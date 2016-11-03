<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Adapter;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

class AdapterAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager;

    /**
     * Set up service manager and database configuration.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->serviceManager = new ServiceManager();

        $config = new Config([
            'abstract_factories' => ['Zend\Db\Adapter\AdapterAbstractServiceFactory']
        ]);
        $config->configureServiceManager($this->serviceManager);

        $this->serviceManager->setService('config', [
            'db' => [
                'adapters' => [
                    'Zend\Db\Adapter\Writer' => [
                        'driver' => 'mysqli',
                    ],
                    'Zend\Db\Adapter\Reader' => [
                        'driver' => 'mysqli',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return array
     */
    public function providerValidService()
    {
        return [
            ['Zend\Db\Adapter\Writer'],
            ['Zend\Db\Adapter\Reader'],
        ];
    }

    /**
     * @return array
     */
    public function providerInvalidService()
    {
        return [
            ['Zend\Db\Adapter\Unknown'],
        ];
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @requires extension mysqli
     */
    public function testValidService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('Zend\Db\Adapter\Adapter', $actual);
    }

    /**
     * @param string $service
     * @dataProvider providerInvalidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testInvalidService($service)
    {
        $actual = $this->serviceManager->get($service);
    }

    public function testInjectSqlBuilder()
    {
        $mockBuilder = $this->getMock('Zend\Db\Adapter\SqlBuilderInterface');
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('sqlBuilderAlias', $mockBuilder);
        $this->serviceManager->setService('config', [
            'db' => [
                'adapters' => [
                    'Zend\Db\Adapter\Writer' => [
                        'driver' => 'mysqli',
                        'sql_builder' => 'sqlBuilderAlias',
                    ],
                    'Zend\Db\Adapter\Reader' => [
                        'driver' => 'mysqli',
                        'sql_builder' => 'sqlBuilderAlias',
                    ],
                ],
            ],
        ]);

        $adapter = $this->serviceManager->get('Zend\Db\Adapter\Writer');
        $this->assertInstanceOf('Zend\Db\Adapter\Adapter', $adapter);
        $this->assertSame($mockBuilder, $adapter->getSqlBuilder());
    }
}
