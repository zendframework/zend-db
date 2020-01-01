<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace ZendIntegrationTest\Db\Adapter\Driver\Oci8;

trait TraitSetup
{
    protected $variables = [
        'connectionstring' => 'TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CONNECTIONSTRING',
        'username' => 'TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_USERNAME',
        'password' => 'TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_PASSWORD',
        'charset' => 'TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CHARSET',
        'database' => 'TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_DATABASE',
    ];
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8')) {
            $this->markTestSkipped('Oci8 integration test disabled');
        }
        
        if (!extension_loaded('oci8')) {
            $this->fail('The phpunit group integration-oci8 was enabled, but the extension is not loaded.');
        }
        
        foreach ($this->variables as $name => $value) {
            if (!getenv($value)) {
                $this->markTestSkipped(sprintf(
                    'Missing required variable %s from phpunit.xml for this integration test',
                    $value
                ));
            }
            $this->variables[$name] = getenv($value);
        }
    }
}
