<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl;

use Zend\Db\Sql\Ddl\DropTable;
use Zend\Db\Sql\TableIdentifier;

class DropTableTest extends \PHPUnit_Framework_TestCase
{
    public function testObjectConstruction()
    {
        $ct = new DropTable('foo');
        $this->assertEquals('foo', $ct->table->getTable());

        $ct = new DropTable(new TableIdentifier('foo'));
        $this->assertEquals('foo', $ct->table->getTable());
    }
}
