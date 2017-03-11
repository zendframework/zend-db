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

        $this->assertEquals(
            "ALTER TABLE [altered]\n".
                " ADD [id] INTEGER FILESTREAM COLLATE [Cyrillic_General_CI_AS] IDENTITY (1, 1) NOT NULL " .
                    "ROWGUIDCOL SPARSE ENCRYPTED WITH (COLUMN_ENCRYPTION_KEY = key_name) " .
                    "MASKED WITH (FUNCTION = ' mask_function ') PRIMARY KEY;\n".
            "ALTER TABLE [altered]\n".
                " ADD [named_pk] INTEGER NOT NULL " .
                    "CONSTRAINT [specified_pk] PRIMARY KEY;",
            $alterDecorator->setSubject($alterTable)->getSqlString($platform)
        );

        // add constraint without columns
        $alterTable = new AlterTable('constrained');
        $alterTable->addConstraint(new PrimaryKey(['u_id', 'g_id'], 'UserGroup_PK'));
        $this->assertEquals(
            "ALTER TABLE [constrained]\n".
                " ADD CONSTRAINT [UserGroup_PK] PRIMARY KEY ([u_id], [g_id]);",
            trim($alterDecorator->setSubject($alterTable)->getSqlString($platform))
        );
    }

    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator::getSqlString
     */
    public function testIdentityBooleanConvertsToDefaultParams()
    {
        $platform = new TrustingSqlServerPlatform();

        $atd = new AlterTableDecorator();
        $at = new AlterTable('identifiable');
        $id = new Column('id');
        $id->setOption('identity', true);
        $at->addColumn($id);

        $this->assertEquals(
            "ALTER TABLE [identifiable]\n".
                " ADD [id] INTEGER IDENTITY (1, 1) NOT NULL;",
            $atd->setSubject($at)->getSqlString($platform)
        );
    }

    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator::getSqlString
     */
    public function testIdentityInvalidFormatThrowsException()
    {
        $platform = new TrustingSqlServerPlatform();

        $atd = new AlterTableDecorator();
        $at = new AlterTable('invalid');

        $id = new Column('id');
        $id->setOption('identity', '1');
        $at->addColumn($id);

        $this->setExpectedException(InvalidArgumentException::class);
        $atd->setSubject($at)->getSqlString($platform);
    }

    /**
     * @covers Zend\Db\Sql\Platform\SqlServer\Ddl\AlterTableDecorator::getSqlString
     */
    public function testVarbinarySyntaxCorrected()
    {
        $platform = new TrustingSqlServerPlatform();

        $atd = new AlterTableDecorator();
        $at = new AlterTable('hasbinarydata');

        $at->addColumn(new Varbinary('binary'));
        $this->assertEquals(
            "ALTER TABLE [hasbinarydata]\n".
                " ADD [binary] VARBINARY (max) NOT NULL;",
            $atd->setSubject($at)->getSqlString($platform)
        );
    }
}
