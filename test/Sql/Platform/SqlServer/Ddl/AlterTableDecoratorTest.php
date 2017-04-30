<?php
/**
 * @see       http://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Db\Sql\Platform\SqlServer\Ddl;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Ddl\Column\Varbinary;
use Zend\Db\Sql\Ddl\Column\Varchar;
use Zend\Db\Sql\Ddl\Constraint\PrimaryKey;
use Zend\Db\Sql\Exception\InvalidArgumentException;
use Zend\Db\Sql\Platform\Sqlserver\Ddl\AlterTableDecorator;
use ZendTest\Db\TestAsset\TrustingSqlServerPlatform;

class AlterTableDecoratorTest extends TestCase
{
    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator::getSqlString
     */
    public function testGetSqlString()
    {
        $platform = new TrustingSqlServerPlatform();

        $alterDecorator = new AlterTableDecorator();
        $alterTable = new AlterTable('altered');

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
        $alterTable->addColumn($id);

        $primaryKey = new Column('named_pk');
        $primaryKey->addConstraint(new PrimaryKey(null, 'specified_pk'));
        $alterTable->addColumn($primaryKey);

        //SQL Server needs separate ALTER command per operation
        $this->assertEquals(
              "ALTER TABLE [altered]\n"
            .    " ADD [id] INTEGER FILESTREAM COLLATE Cyrillic_General_CI_AS IDENTITY (1, 1) NOT NULL "
            .       "ROWGUIDCOL SPARSE ENCRYPTED WITH (COLUMN_ENCRYPTION_KEY = key_name) "
            .       "MASKED WITH (FUNCTION = ' mask_function ') PRIMARY KEY;\n"
            . "ALTER TABLE [altered]\n"
            .    " ADD [named_pk] INTEGER NOT NULL "
            .       "CONSTRAINT [specified_pk] PRIMARY KEY;",
            $alterDecorator->setSubject($alterTable)->getSqlString($platform)
        );

        // add table constraint without column definition
        $alterTable = new AlterTable('constrained');
        $alterTable->addConstraint(new PrimaryKey(['u_id', 'g_id'], 'UserGroup_PK'));
        $this->assertEquals(
              "ALTER TABLE [constrained]\n"
            .   " ADD CONSTRAINT [UserGroup_PK] PRIMARY KEY ([u_id], [g_id]);",
            trim($alterDecorator->setSubject($alterTable)->getSqlString($platform))
        );

        // change column options
        $alterTable = new AlterTable('altered');
        $changedColumn = new Varchar('changed', 10);
        $changedColumn->setOption('COLLATE', 'Cyrillic_General_CI_AS');
        $alterTable->changeColumn('changed', $changedColumn);
        $this->assertEquals(
              "ALTER TABLE [altered]\n"
            . " ALTER COLUMN [changed] VARCHAR(10) COLLATE Cyrillic_General_CI_AS NOT NULL;",
            trim($alterDecorator->setSubject($alterTable)->getSqlString($platform))
        );

        // rename column
        $alterTable = new AlterTable('altered');
        $changedColumn = new Varchar('new_name', 10);
        $alterTable->changeColumn('old_name', $changedColumn);

        // Cannot reliably detect if any other options for column have changed besides name.
        // Therefore, have to run at least most basic ALTER TABLE command that performs a benign change.
        $this->assertEquals(
              "sp_rename 'altered.old_name', 'new_name', 'COLUMN';\n"
            . " ALTER TABLE [altered]\n"
            . " ALTER COLUMN [new_name] VARCHAR(10) NOT NULL;",
            trim($alterDecorator->setSubject($alterTable)->getSqlString($platform))
        );

        // drop columns
        $alterTable = new AlterTable('altered');
        $alterTable->dropColumn('drop_this');
        $this->assertEquals(
              "ALTER TABLE [altered]\n"
            . " DROP COLUMN [drop_this];",
            trim($alterDecorator->setSubject($alterTable)->getSqlString($platform))
        );
    }

    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator::getSqlString
     */
    public function testIdentityBooleanConvertsToDefaultParams()
    {
        $platform = new TrustingSqlServerPlatform();

        $alterDecorator = new AlterTableDecorator();
        $alterTable = new AlterTable('identifiable');
        $id = new Column('id');
        $id->setOption('identity', true);
        $alterTable->addColumn($id);

        $this->assertEquals(
              "ALTER TABLE [identifiable]\n"
            .   " ADD [id] INTEGER IDENTITY (1, 1) NOT NULL;",
            $alterDecorator->setSubject($alterTable)->getSqlString($platform)
        );
    }

    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator::getSqlString
     */
    public function testIdentityInvalidFormatThrowsException()
    {
        $platform = new TrustingSqlServerPlatform();

        $alterDecorator = new AlterTableDecorator();
        $alterTable = new AlterTable('invalid');

        $id = new Column('id');
        $id->setOption('identity', '1');
        $alterTable->addColumn($id);

        $this->setExpectedException(InvalidArgumentException::class);
        $alterDecorator->setSubject($alterTable)->getSqlString($platform);
    }

    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator::getSqlString
     */
    public function testVarbinarySyntaxCorrected()
    {
        $platform = new TrustingSqlServerPlatform();

        $alterDecorator = new AlterTableDecorator();
        $alterTable = new AlterTable('hasbinarydata');

        $alterTable->addColumn(new Varbinary('binary'));
        $this->assertEquals(
              "ALTER TABLE [hasbinarydata]\n"
            .   " ADD [binary] VARBINARY (max) NOT NULL;",
            $alterDecorator->setSubject($alterTable)->getSqlString($platform)
        );
    }
}
