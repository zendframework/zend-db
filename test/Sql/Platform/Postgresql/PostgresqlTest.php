<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Platform;

use Zend\Db\Sql\Platform\Postgresql\Postgresql;
use Zend\Db\Sql\Platform\Postgresql\Ddl;

class PostgresqlTest extends \PHPUnit_Framework_TestCase
{
    /*
    * @testdox unit test / object test: Has CreateTable proxy
    * @covers Zend\Db\Sql\Platform\Postgresql\Postgresql::__construct
    */
    public function testConstruct()
    {
        $postgresql = new Postgresql();
        $decorators = $postgresql->getDecorators();

        $this->assertArrayHasKey('Zend\Db\Sql\Ddl\CreateTable', $decorators);
        $this->assertInstanceOf(Ddl\CreateTableDecorator::class, $decorators['Zend\Db\Sql\Ddl\CreateTable']);
        $this->assertArrayHasKey('Zend\Db\Sql\Ddl\AlterTable', $decorators);
        $this->assertInstanceOf(Ddl\AlterTableDecorator::class, $decorators['Zend\Db\Sql\Ddl\AlterTable']);
    }
}
