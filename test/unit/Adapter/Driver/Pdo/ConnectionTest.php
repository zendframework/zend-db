<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Adapter\Driver\Pdo;

use Exception;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Driver\Pdo\Connection;

class ConnectionTest extends TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->connection = new Connection();
    }

    /**
     * Test getResource method tries to connect to  the database, it should never return null
     *
     * @covers \Zend\Db\Adapter\Driver\Pdo\Connection::getResource
     */
    public function testResource()
    {
        $this->expectException('Zend\Db\Adapter\Exception\InvalidConnectionParametersException');
        $this->connection->getResource();
    }

    /**
     * Test getConnectedDsn returns a DSN string if it has been set
     *
     * @covers \Zend\Db\Adapter\Driver\Pdo\Connection::getDsn
     */
    public function testGetDsn()
    {
        $dsn = "sqlite::memory:";
        $this->connection->setConnectionParameters(['dsn' => $dsn]);
        try {
            $this->connection->connect();
        } catch (Exception $e) {
        }
        $responseString = $this->connection->getDsn();

        self::assertEquals($dsn, $responseString);
    }

    /**
     * @group 2622
     */
    public function testArrayOfConnectionParametersCreatesCorrectDsn()
    {
        $this->connection->setConnectionParameters([
            'driver'  => 'pdo_mysql',
            'charset' => 'utf8',
            'dbname'  => 'foo',
            'port'    => '3306',
            'unix_socket' => '/var/run/mysqld/mysqld.sock',
        ]);
        try {
            $this->connection->connect();
        } catch (Exception $e) {
        }
        $responseString = $this->connection->getDsn();

        self::assertStringStartsWith('mysql:', $responseString);
        self::assertContains('charset=utf8', $responseString);
        self::assertContains('dbname=foo', $responseString);
        self::assertContains('port=3306', $responseString);
        self::assertContains('unix_socket=/var/run/mysqld/mysqld.sock', $responseString);
    }

    public function testHostnameAndUnixSocketThrowsInvalidConnectionParametersException()
    {
        $this->expectException('Zend\Db\Adapter\Exception\InvalidConnectionParametersException');
        $this->expectExceptionMessage(
            'Ambiguous connection parameters, both hostname and unix_socket parameters were set'
        );

        $this->connection->setConnectionParameters([
            'driver'  => 'pdo_mysql',
            'host'    => '127.0.0.1',
            'dbname'  => 'foo',
            'port'    => '3306',
            'unix_socket' => '/var/run/mysqld/mysqld.sock',
        ]);
        $this->connection->connect();
    }

    public function testDblibArrayOfConnectionParametersCreatesCorrectDsn()
    {
        $this->connection->setConnectionParameters([
            'driver'  => 'pdo_dblib',
            'charset' => 'UTF-8',
            'dbname'  => 'foo',
            'port'    => '1433',
            'version' => '7.3',
        ]);
        try {
            $this->connection->connect();
        } catch (Exception $e) {
        }
        $responseString = $this->connection->getDsn();

        $this->assertStringStartsWith('dblib:', $responseString);
        $this->assertContains('charset=UTF-8', $responseString);
        $this->assertContains('dbname=foo', $responseString);
        $this->assertContains('port=1433', $responseString);
        $this->assertContains('version=7.3', $responseString);
    }

    public function testNestedTransactionsNoErrors()
    {
        $dsn = 'sqlite::memory:';
        $this->connection->setConnectionParameters(['dsn' => $dsn]);
        $this->connection->connect();

        $nestedTransaction = static function (Connection $connection) {
            $connection->beginTransaction();
            try {
                throw new Exception('Custom exception message!');
            } catch (Exception $ex) {
                $connection->rollback();
                throw $ex;
            }
        };

        $parentTransaction = static function (Connection $connection) use ($nestedTransaction) {
            $connection->beginTransaction();
            try {
                $nestedTransaction($connection);
                $connection->commit();
            } catch (Exception $ex) {
                $connection->rollback();
                throw $ex;
            }
        };

        $this->expectExceptionMessage('Custom exception message');
        $parentTransaction($this->connection);
    }

    public function testNestedTransactionErrorOnParentCommitIfNestedRollback()
    {
        $dsn = 'sqlite::memory:';
        $this->connection->setConnectionParameters(['dsn' => $dsn]);
        $this->connection->connect();

        $this->connection->beginTransaction();
        $this->connection->beginTransaction();
        $this->connection->rollback();

        // As PDO does not support nested transaction this commit cannot be successful.
        // Everything what was executed between opening transactions is rollbacked.
        $this->expectExceptionMessage('There is no active transaction');
        $this->connection->commit();
    }

    public function testNestedTransactionNoErrorsOnParentAndNestedRollback()
    {
        $dsn = 'sqlite::memory:';
        $this->connection->setConnectionParameters(['dsn' => $dsn]);
        $this->connection->connect();

        $this->connection->beginTransaction();
        $this->connection->beginTransaction();
        $this->connection->rollback();
        $this->connection->rollback();
    }
}
