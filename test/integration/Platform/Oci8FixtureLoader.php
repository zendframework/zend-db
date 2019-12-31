<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace ZendIntegrationTest\Db\Platform;

use RuntimeException;

class Oci8FixtureLoader implements FixtureLoader
{
    /** @var string */
    private $fixtureFile = __DIR__ . '/../TestFixtures/oci8.sql';

    /** @var resource */
    private $oci8;

    /** @var bool */
    private $initialRun = true;
    
    public function createDatabase()
    {
        $this->oci8 = oci_pconnect(
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_USERNAME'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_PASSWORD'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CONNECTIONSTRING'),
            getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_OCI8_CHARSET'),
            null
        );
        
        $this->dropDatabase();

        $sql = file_get_contents($this->fixtureFile);
        $sql2 = <<<SQL
        $sql
SQL;
        
        $resource = oci_parse($this->oci8, $sql2);
        $ret = oci_execute($resource);
        if (false === $ret) {
            throw new RuntimeException(sprintf(
                'I cannot create the table for database %s. Check the %s file. %s',
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
