<?php
/**
 * @see       http://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Db\Sql\Platform\SqlServer\Ddl;

use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Ddl\Constraint\PrimaryKey;
use PHPUnit\Framework\TestCase;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Platform\SqlServer\Ddl\CreateTableDecorator;
use ZendTest\Db\TestAsset\TrustingSqlServerPlatform;

class CreateTableDecoratorTest extends TestCase
{
    /**
     * @covers \Zend\Db\Sql\Platform\SqlServer\Ddl\CreateTableDecorator::getSqlString
     */
    public function testGetSqlString()
    {
        $platform = new TrustingSqlServerPlatform();

        $createDecorator = new CreateTableDecorator();

        $createTable = new CreateTable('foo');
        self::assertEquals(
            "CREATE TABLE [foo] ( \n     \n)",
            $createDecorator->setSubject($createTable)->getSqlString($platform)
        );

        $createTable = new CreateTable('foo', true);
        self::assertEquals(
            "CREATE TABLE [#foo] ( \n     \n)",
            $createDecorator->setSubject($createTable)->getSqlString($platform)
        );

        $createTable = new CreateTable('foo');
        $createTable->addColumn(new Column('bar'));
        self::assertEquals(
            "CREATE TABLE [foo] ( \n    [bar] INTEGER NOT NULL \n)",
            $createDecorator->setSubject($createTable)->getSqlString($platform)
        );

        $createTable = new CreateTable('foo', true);
        $createTable->addColumn(new Column('bar'));
        self::assertEquals(
            "CREATE TABLE [#foo] ( \n    [bar] INTEGER NOT NULL \n)",
            $createDecorator->setSubject($createTable)->getSqlString($platform)
        );

        $createTable = new CreateTable('opinionated');
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
        $createTable->addColumn($id);

        $primaryKey = new Column('named_key');
        $primaryKey->addConstraint(new PrimaryKey(null, 'specified_primary_key_name'));
        $createTable->addColumn($primaryKey);

        self::assertEquals(
            "CREATE TABLE [opinionated] ( \n" .
            "    [id] INTEGER FILESTREAM COLLATE Cyrillic_General_CI_AS IDENTITY (1, 1) NOT NULL " .
                    "ROWGUIDCOL SPARSE ENCRYPTED WITH (COLUMN_ENCRYPTION_KEY = key_name) " .
                    "MASKED WITH (FUNCTION = ' mask_function ') " .
                    "PRIMARY KEY,\n" .
            "    [named_key] INTEGER NOT NULL CONSTRAINT [specified_primary_key_name] PRIMARY KEY \n" .
            ")",
            $createDecorator->setSubject($createTable)->getSqlString($platform)
        );
    }
}
