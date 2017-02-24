<?php

/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Platform\SqlServer\Ddl;

use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator;
use ZendTest\Db\TestAsset\TrustingSqlServerPlatform;

class AlterTableDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator::getSqlString
     */
    public function testGetSqlString()
    {
        $platform = new TrustingSqlServerPlatform();

        $ctd = new AlterTableDecorator();

        $ct = new AlterTable('altered');
        $ct->addColumn(new Column('addedColumn'));
        $this->assertEquals(
            "ALTER TABLE [altered]\n".
                ' ADD [addedColumn] INTEGER NOT NULL',
            $ctd->setSubject($ct)->getSqlString($platform)
        );
    }
}
