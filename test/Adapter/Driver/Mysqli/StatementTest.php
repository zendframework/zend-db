<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Adapter\Driver\Mysqli;

use Zend\Db\Adapter\Driver\Mysqli\Statement;

use Zend\Db\Adapter\Driver\Mysqli\Connection;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Exception;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Profiler;

class MysliTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->statement = new Statement();
    }

    public function testPrepare()
    {
        $this->statement->initialize(new TestAsset\MysqliMock());
        $this->assertNull($this->statement->prepare('SELECT 1'));
    }

    /**
     * intentionally throw exception to test error contains valid string
     */
    // public function testMysqliStatementPrepareException(){
    //   $this->statement = new Statement(new Connection());
    //   // $this->statement->initialize(new mysqli("localhost", "test", "test", "test", 3306));

    //   // $this->_connection = mysqli_connect('localhost', 'root', ''); 
    //   // $this->_execute('USE playpen');
    //   $this->statement->setSql('SELECT * FROM test WHERE;');
    //   try{
    //     $this->statement->prepare();
    //   // } catch (InvalidQueryException $e){
    //   } catch (Exception $e){
    //     fwrite(STDOUT, $e->getMessage());
    //   }
    //   // fwrite(STDOUT, $this->statement->getSql());
    // }
}