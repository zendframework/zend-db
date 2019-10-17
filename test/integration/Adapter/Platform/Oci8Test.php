<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendIntegrationTest\Db\Adapter\Platform;

use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Driver\Oci8;
use Zend\Db\Adapter\Driver\Pdo;
use Zend\Db\Adapter\Platform\Oracle;

/**
 * @group integration
 * @group integration-oci8
 */
class Oci8Test extends TestCase
{
    public $adapters = [];
    
    public function setUp()
    {
        if (!getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8')) {
            $this->markTestSkipped(__CLASS__ . ' integration tests are not enabled!');
        }
        if (extension_loaded('oci8')) {
            $this->adapters['oci8'] = oci_connect(
                getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_USERNAME'),
                getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_PASSWORD'),
                getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CONNECTIONSTRING'),
                getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CHARSET'),
                null);
        }
        if (extension_loaded('pdo_oci')) {
            $this->adapters['pdo_oci'] = new \PDO(
                'oci:dbname=' . getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CONNECTIONSTRING'),
                getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_USERNAME'),
                getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_PASSWORD')
            );
        }
    }
    
    public function testQuoteValueWithOci8()
    {
        if (!isset($this->adapters['oci8'])
            || !$this->adapters['oci8'] instanceof \Oracle) {
            $this->markTestSkipped('Oracle (oci8) not configured in unit test configuration file');
        }
        $oracle = new Oracle($this->adapters['oci8']);
        $value = $oracle->quoteValue('value');
        self::assertEquals('\'value\'', $value);
        
        $oracle = new Oracle(new Oci8\Oci8(new Oci8\Connection($this->adapters['oci8'])));
        $value = $oracle->quoteValue('value');
        self::assertEquals('\'value\'', $value);
    }
    
    public function testQuoteValueWithPdoOci()
    {
        if (!isset($this->adapters['pdo_oci'])
            || !$this->adapters['pdo_oci'] instanceof \PDO) {
            $this->markTestSkipped('Oracle (pdo_oci) not configured in unit test configuration file');
        }
        $oracle = new Pdo($this->adapters['pdo_oci']);
        $value = $oracle->quoteValue('value');
        self::assertEquals('\'value\'', $value);
        
        $oracle = new Pdo(new Pdo\Pdo(new Pdo\Connection($this->adapters['pdo_oci'])));
        $value = $oracle->quoteValue('value');
        self::assertEquals('\'value\'', $value);
    }
    
    
}
