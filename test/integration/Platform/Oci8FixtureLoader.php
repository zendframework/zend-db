<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendIntegrationTest\Db\Platform;

class Oci8FixtureLoader implements FixtureLoader
{
    
    private $fixtureFile = __DIR__ . '/../TestFixtures/oci8.sql';
    
    private $oci8;
    private $initialRun = true;
    
    public function createDatabase()
    {
        /*var_dump(getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_USERNAME'));
        var_dump(getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_PASSWORD'));
        var_dump(getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CONNECTIONSTRING'));
        var_dump(getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CHARSET'));
        die();*/
        $this->oci8 = oci_pconnect(
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_USERNAME'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_PASSWORD'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CONNECTIONSTRING'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CHARSET'),
            null);
        
        
        $this->dropDatabase();
        /*if (false === $this->oci8->exec(sprintf(
            "CREATE DATABASE %s",
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_DATABASE')
        ))) {
            throw new \Exception(sprintf(
                "I cannot create the Oci8 %s test database: %s",
                getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_DATABASE'),
                print_r(oci_error($this->oci8), true)
            ));
        }*/
        
        
        /*unset($this->oci8);
        $this->oci8 = oci_connect(
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_USERNAME'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_PASSWORD'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CONNECTIONSTRING'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CHARSET'),
            null);*/
        
        $sql = file_get_contents($this->fixtureFile);
        $sql2 = <<<SQL
        $sql
SQL;
        
        $resource = oci_parse($this->oci8, $sql2);
        $ret = oci_execute($resource);
        if (false === $ret) {
            throw new \Exception(sprintf(
                "I cannot create the table for database %s. Check the %s file. %s ",
                getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_DATABASE'),
                $this->fixtureFile,
                print_r(oci_error($this->oci8), true)
            ));
        }
    }
    
    public function dropDatabase()
    {
        if (!$this->initialRun) {
            // Not possible to drop database in Oracle.
            
            return;
        }
        $this->initialRun = false;
        
        $resource = oci_parse($this->oci8, 'DROP TABLE TEST CASCADE CONSTRAINTS');
        oci_execute($resource);
        
        $resource = oci_parse($this->oci8, 'DROP TABLE TEST_CHARSET CASCADE CONSTRAINTS');
        oci_execute($resource);
        
        $resource = oci_parse($this->oci8, 'DROP SEQUENCE test_sequence');
        oci_execute($resource);
        
        $resource = oci_parse($this->oci8, 'DROP SEQUENCE testcharset_sequence');
        oci_execute($resource);
        
        $resource = oci_parse($this->oci8, 'DROP TRIGGER TEST_ON_INSERT');
        oci_execute($resource);
        
        $resource = oci_parse($this->oci8, 'DROP TRIGGER TESTCHARSET_ON_INSERT');
        oci_execute($resource);
    }
}
