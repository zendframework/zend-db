<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Platform\Mysql\Ddl;

use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Platform\Mysql;
use Zend\Db\Metadata\Object\ConstraintObject;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Ddl\Constraint\PrimaryKey;
use Zend\Db\Sql\Ddl\Constraint\UniqueKey;
use Zend\Db\Sql\Platform\Mysql\Ddl\AlterTableDecorator;

class AlterTableDecoratorTest extends TestCase
{
    /**
     * @covers \Zend\Db\Sql\Platform\Mysql\Ddl\AlterTableDecorator::setSubject
     */
    public function testSetSubject()
    {
        $ctd = new AlterTableDecorator();
        $ct = new AlterTable;
        self::assertSame($ctd, $ctd->setSubject($ct));
    }

    /**
     * @covers \Zend\Db\Sql\Platform\Mysql\Ddl\AlterTableDecorator::getSqlString
     */
    public function testGetSqlString()
    {
        $ctd = new AlterTableDecorator();
        $ct = new AlterTable('foo');
        $ctd->setSubject($ct);

        $col = new Column('bar');
        $col->setOption('zerofill', true);
        $col->setOption('unsigned', true);
        $col->setOption('identity', true);
        $col->setOption('comment', 'baz');
        $col->setOption('after', 'bar');
        $col->addConstraint(new PrimaryKey());
        $ct->addColumn($col);

        $fk = new ConstraintObject('my_fk', null);
        $fk->setType('FOREIGN KEY');
        $ct->dropConstraint($fk);

        $ct->dropConstraint(new UniqueKey(null, 'my_unique_index'));

        $this->assertEquals(
            "ALTER TABLE `foo`\n ADD COLUMN `bar` INTEGER UNSIGNED ZEROFILL " .
            "NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'baz' AFTER `bar`,\n" .
            "    DROP FOREIGN KEY `my_fk`,\n" .
            " DROP KEY `my_unique_index`",
            @$ctd->getSqlString(new Mysql())
        );
    }
}
