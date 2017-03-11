<?php

/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Platform\SqlServer\Ddl;

use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Ddl\Constraint\PrimaryKey;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Platform\SqlServer\Ddl\CreateTableDecorator;
use ZendTest\Db\TestAsset\TrustingSqlServerPlatform;

class CreateTableDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\CreateTableDecorator::getSqlString
     */
    public function testGetSqlString()
    {
        $platform = new TrustingSqlServerPlatform();

        $ctd = new CreateTableDecorator();

        $ct = new CreateTable('foo');
        $this->assertEquals("CREATE TABLE [foo] ( \n     \n)", $ctd->setSubject($ct)->getSqlString($platform));

        $ct = new CreateTable('foo', true);
        $this->assertEquals("CREATE TABLE [#foo] ( \n     \n)", $ctd->setSubject($ct)->getSqlString($platform));

        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('bar'));
        $this->assertEquals("CREATE TABLE [foo] ( \n    [bar] INTEGER NOT NULL \n)", $ctd->setSubject($ct)->getSqlString($platform));

        $ct = new CreateTable('foo', true);
        $ct->addColumn(new Column('bar'));
        $this->assertEquals("CREATE TABLE [#foo] ( \n    [bar] INTEGER NOT NULL \n)", $ctd->setSubject($ct)->getSqlString($platform));

        $ct = new CreateTable('opinionated');
        // test for valid syntax, not valid engine semantics
        $id = new Column('id');
        $id->addConstraint(new PrimaryKey());
        $id->setOption('filestream', true);
        $id->setOption('collate', 'Cyrillic_General_CI_AS');
        $id->setOption('rowguidcol', true);
        $id->setOption('sparse', true);
        $id->setOption('encrypted with', '(COLUMN_ENCRYPTION_KEY = key_name)');
        $id->setOption('masked with', "(FUNCTION = ' mask_function ')");
        $id->setOption('identity', '(1, 1)');
        $ct->addColumn($id);

        $pk = new Column('named_pk');
        $pk->addConstraint(new PrimaryKey(null, 'specified_pk'));
        $ct->addColumn($pk);

        $this->assertEquals(
            "CREATE TABLE [opinionated] ( \n".
            "    [id] INTEGER FILESTREAM COLLATE [Cyrillic_General_CI_AS] IDENTITY (1, 1) NOT NULL ROWGUIDCOL SPARSE ENCRYPTED WITH (COLUMN_ENCRYPTION_KEY = key_name) MASKED WITH (FUNCTION = ' mask_function ') PRIMARY KEY,\n".
            "    [named_pk] INTEGER NOT NULL CONSTRAINT [specified_pk] PRIMARY KEY \n".
            ')',
            $ctd->setSubject($ct)->getSqlString($platform)
        );
    }
}
