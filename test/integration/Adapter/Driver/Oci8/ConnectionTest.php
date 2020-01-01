<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

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
