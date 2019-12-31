<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace ZendIntegrationTest\Db\Adapter\Driver\Oci8;

use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\TableGateway\TableGateway;

class TableGatewayTest extends TestCase
{
    use TraitSetup;
    
    /**
     * @see https://github.com/zendframework/zend-db/issues/330
     */
    public function testSelectWithEmptyCurrentWithBufferResult()
    {
        $adapter = new Adapter([
            'driver' => 'OCI8',
            'connection_string' => $this->variables['connectionstring'],
            'username' => $this->variables['username'],
            'password' => $this->variables['password'],
            'character_set' => $this->variables['charset'],
            'options' => ['buffer_results' => true]
        ]);
        $tableGateway = new TableGateway('TEST', $adapter);
        $rowset = $tableGateway->select('ID = 0');
        
        $this->assertNull($rowset->current());
    }
    
    /**
     * @see https://github.com/zendframework/zend-db/issues/330
     */
    public function testSelectWithEmptyCurrentWithoutBufferResult()
    {
        $adapter = new Adapter([
            'driver' => 'OCI8',
            'connection_string' => $this->variables['connectionstring'],
            'username' => $this->variables['username'],
            'password' => $this->variables['password'],
            'character_set' => $this->variables['charset'],
            'options' => ['buffer_results' => false]
        ]);
        $tableGateway = new TableGateway('TEST', $adapter);
        $rowset = $tableGateway->select('ID = 0');
        
        $this->assertNull($rowset->current());
    }
    
    /**
     * @see https://github.com/zendframework/zend-db/pull/396
     */
    public function testBlobWithOci8()
    {
        $adapter = new Adapter([
            'driver' => 'OCI8',
            'connection_string' => $this->variables['connectionstring'],
            'username' => $this->variables['username'],
            'password' => $this->variables['password'],
            'character_set' => $this->variables['charset'],
            'options' => ['buffer_results' => false]
        ]);
        $tableGateway = new TableGateway('TEST', $adapter);
        
        $blob = 'very long sentence that is in fact not very long that tests blob';
        
        $data = new ParameterContainer();
        $data->setFromArray(['CONTENT_BLOB' => $blob]);
        $data->offsetSetErrata('CONTENT_BLOB', ParameterContainer::TYPE_BLOB);
        
        $sql = 'UPDATE TEST SET CONTENT_BLOB = :CONTENT_BLOB WHERE ID = 1';
        $stm = $tableGateway->getAdapter()->getDriver()->createStatement($sql);
        $stm->setParameterContainer($data);
        $stm->execute();
        
        $rowset = $tableGateway->select('ID = 1')->current();
        
        $this->assertInstanceOf('OCI-Lob', $rowset['CONTENT_BLOB']);
        $value = $rowset['CONTENT_BLOB']->read($rowset['CONTENT_BLOB']->size());
        $this->assertEquals($blob, $value);
    }
    
    /**
     * @see https://github.com/zendframework/zend-db/pull/396
     */
    public function testClobWithOci8()
    {
        $adapter = new Adapter([
            'driver' => 'OCI8',
            'connection_string' => $this->variables['connectionstring'],
            'username' => $this->variables['username'],
            'password' => $this->variables['password'],
            'character_set' => $this->variables['charset'],
            'options' => ['buffer_results' => false]
        ]);
        $tableGateway = new TableGateway('TEST', $adapter);
        
        $clob = 'very long sentence that is in fact not very long that tests clob';
        
        $data = new ParameterContainer();
        $data->setFromArray(['CONTENT_CLOB' => $clob]);
        $data->offsetSetErrata('CONTENT_CLOB', ParameterContainer::TYPE_CLOB);
        
        $sql = 'UPDATE TEST SET CONTENT_CLOB = :CONTENT_CLOB WHERE ID = 1';
        $stm = $tableGateway->getAdapter()->getDriver()->createStatement($sql);
        $stm->setParameterContainer($data);
        $stm->execute();
        
        $rowset = $tableGateway->select('ID = 1')->current();
        
        $this->assertInstanceOf('OCI-Lob', $rowset['CONTENT_CLOB']);
        $value = $rowset['CONTENT_CLOB']->read($rowset['CONTENT_CLOB']->size());
        $this->assertEquals($clob, $value);
    }
    
    /**
     * @see https://github.com/zendframework/zend-db/pull/396
     */
    public function testLobWithOci8()
    {
        $adapter = new Adapter([
            'driver' => 'OCI8',
            'connection_string' => $this->variables['connectionstring'],
            'username' => $this->variables['username'],
            'password' => $this->variables['password'],
            'character_set' => $this->variables['charset'],
            'options' => ['buffer_results' => false]
        ]);
        $tableGateway = new TableGateway('TEST', $adapter);
        
        $clob = 'very long sentence that is in fact not very long that tests lob';
        
        $data = new ParameterContainer();
        $data->setFromArray(['CONTENT_CLOB' => $clob]);
        $data->offsetSetErrata('CONTENT_CLOB', ParameterContainer::TYPE_LOB);
        
        $sql = 'UPDATE TEST SET CONTENT_CLOB = :CONTENT_CLOB WHERE ID = 2';
        $stm = $tableGateway->getAdapter()->getDriver()->createStatement($sql);
        $stm->setParameterContainer($data);
        $stm->execute();
        
        $rowset = $tableGateway->select('ID = 2')->current();
        
        $this->assertInstanceOf('OCI-Lob', $rowset['CONTENT_CLOB']);
        $value = $rowset['CONTENT_CLOB']->read($rowset['CONTENT_CLOB']->size());
        $this->assertEquals($clob, $value);
    }
}
