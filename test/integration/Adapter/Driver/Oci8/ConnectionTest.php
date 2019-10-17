<?php

namespace ZendIntegrationTest\Db\Adapter\Driver\Oci8;

use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Driver\Oci8\Connection;

/**
 * @group integration
 * @group integration-oci8
 */
class ConnectionTest extends TestCase
{
    
    use TraitSetup;
    
    public function testConnectionOk()
    {
        $connection = new Connection($this->variables);
        $connection->connect();
        
        self::assertTrue($connection->isConnected());
        $connection->disconnect();
    }
}
