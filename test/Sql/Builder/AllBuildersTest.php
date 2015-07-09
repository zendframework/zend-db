<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql;

class AllBuildersTest extends AbstractTestCase
{
    /**
     * @param type $data
     * @dataProvider dataProvider
     */
    public function test($sqlObject, $platform, $expected)
    {
        $this->assertBuilder($sqlObject, $platform, $expected);
    }

    public function dataProvider()
    {
        return $this->prepareDataProvider(
            $this->dataProvider_CommonProcessMethods(),
            $this->dataProvider_Builders()
        );
    }

    protected function dataProvider_CommonProcessMethods()
    {
        return [
            'Select::processOffset()' => [
                'sqlObject' => $this->select('foo')->offset(10),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" OFFSET \'10\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" OFFSET ?',
                        'parameters' => ['offset' => 10],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 18446744073709551615 OFFSET 10',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT 18446744073709551615 OFFSET ?',
                        'parameters' => ['offset' => 10],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT b.*, rownum b_rownum FROM ( SELECT "foo".* FROM "foo" ) b ) WHERE b_rownum > (10)',
                        'prepare'    => 'SELECT * FROM (SELECT b.*, rownum b_rownum FROM ( SELECT "foo".* FROM "foo" ) b ) WHERE b_rownum > (:offset)',
                        'parameters' => ['offset' => 10],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN 10+1 AND 0+10',
                        'prepare'    => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN ?+1 AND ?+?',
                        'parameters' => ['offset' => 10, 'limit' => null, 'offsetForSum' => 10],
                    ],
                ],
            ],
            'Select::processLimit()' => [
                'sqlObject' => $this->select('foo')->limit(10),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" LIMIT \'10\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" LIMIT ?',
                        'parameters' => ['limit' => 10],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 10',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT ?',
                        'parameters' => ['limit' => 10],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT b.*, rownum b_rownum FROM ( SELECT "foo".* FROM "foo" ) b WHERE rownum <= (0+10)) WHERE b_rownum >= (0 + 1)',
                        'prepare'    => 'SELECT * FROM (SELECT b.*, rownum b_rownum FROM ( SELECT "foo".* FROM "foo" ) b WHERE rownum <= (:offset+:limit)) WHERE b_rownum >= (:offset + 1)',
                        'parameters' => ['offset' => 0, 'limit' => 10],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN 0+1 AND 10+0',
                        'prepare'    => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN ?+1 AND ?+?',
                        'parameters' => ['offset' => null, 'limit' => 10, 'offsetForSum' => null],
                    ],
                ],
            ],
            'Select::processLimitOffset()' => [
                'sqlObject' => $this->select('foo')->limit(10)->offset(5),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" LIMIT \'10\' OFFSET \'5\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => 10, 'offset' => 5],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 10 OFFSET 5',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => 10, 'offset' => 5],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT b.*, rownum b_rownum FROM ( SELECT "foo".* FROM "foo" ) b WHERE rownum <= (5+10)) WHERE b_rownum >= (5 + 1)',
                        'prepare'    => 'SELECT * FROM (SELECT b.*, rownum b_rownum FROM ( SELECT "foo".* FROM "foo" ) b WHERE rownum <= (:offset+:limit)) WHERE b_rownum >= (:offset + 1)',
                        'parameters' => ['offset' => 5, 'limit' => 10],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN 5+1 AND 10+5',
                        'prepare'    => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN ?+1 AND ?+?',
                        'parameters' => ['offset' => 5, 'limit' => 10, 'offsetForSum' => 5],
                    ],
                ],
            ],
            // Github issue https://github.com/zendframework/zend-db/issues/98
            'Select::processJoinNoJoinedColumns()' => [
                'sqlObject' => $this->select('my_table')
                                    ->join('joined_table2', 'my_table.id = joined_table2.id', $columns=[])
                                    ->join('joined_table3', 'my_table.id = joined_table3.id', [\Zend\Db\Sql\Select::SQL_STAR])
                                    ->columns([
                                        'my_table_column',
                                        'aliased_column' => new \Zend\Db\Sql\Expression('NOW()')
                                    ]),
                'expected' => [
                    'sql92' => [
                        'string' => 'SELECT "my_table"."my_table_column" AS "my_table_column", NOW() AS "aliased_column", "joined_table3".* FROM "my_table" INNER JOIN "joined_table2" ON "my_table"."id" = "joined_table2"."id" INNER JOIN "joined_table3" ON "my_table"."id" = "joined_table3"."id"',
                    ],
                    'MySql' => [
                        'string' => 'SELECT `my_table`.`my_table_column` AS `my_table_column`, NOW() AS `aliased_column`, `joined_table3`.* FROM `my_table` INNER JOIN `joined_table2` ON `my_table`.`id` = `joined_table2`.`id` INNER JOIN `joined_table3` ON `my_table`.`id` = `joined_table3`.`id`',
                    ],
                    'Oracle' => [
                        'string' => 'SELECT "my_table"."my_table_column" AS "my_table_column", NOW() AS "aliased_column", "joined_table3".* FROM "my_table" INNER JOIN "joined_table2" ON "my_table"."id" = "joined_table2"."id" INNER JOIN "joined_table3" ON "my_table"."id" = "joined_table3"."id"',
                    ],
                    'SqlServer' => [
                        'string' => 'SELECT [my_table].[my_table_column] AS [my_table_column], NOW() AS [aliased_column], [joined_table3].* FROM [my_table] INNER JOIN [joined_table2] ON [my_table].[id] = [joined_table2].[id] INNER JOIN [joined_table3] ON [my_table].[id] = [joined_table3].[id]',
                    ]
                ]
            ],
            'Select::processJoin()' => [
                'sqlObject' => $this->select('a')->join(['b'=>$this->select('c')->where(['cc'=>10])], 'd=e')->where(['x'=>20]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "a".*, "b".* FROM "a" INNER JOIN (SELECT "c".* FROM "c" WHERE "cc" = \'10\') AS "b" ON "d"="e" WHERE "x" = \'20\'',
                        'prepare'    => 'SELECT "a".*, "b".* FROM "a" INNER JOIN (SELECT "c".* FROM "c" WHERE "cc" = ?) AS "b" ON "d"="e" WHERE "x" = ?',
                        'parameters' => ['subselect1expr1'=>10, 'expr1'=>20],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `a`.*, `b`.* FROM `a` INNER JOIN (SELECT `c`.* FROM `c` WHERE `cc` = \'10\') AS `b` ON `d`=`e` WHERE `x` = \'20\'',
                        'prepare'    => 'SELECT `a`.*, `b`.* FROM `a` INNER JOIN (SELECT `c`.* FROM `c` WHERE `cc` = ?) AS `b` ON `d`=`e` WHERE `x` = ?',
                        'parameters' => ['subselect1expr1'=>10, 'expr1'=>20],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT "a".*, "b".* FROM "a" INNER JOIN (SELECT "c".* FROM "c" WHERE "cc" = \'10\') "b" ON "d"="e" WHERE "x" = \'20\'',
                        'prepare'    => 'SELECT "a".*, "b".* FROM "a" INNER JOIN (SELECT "c".* FROM "c" WHERE "cc" = ?) "b" ON "d"="e" WHERE "x" = ?',
                        'parameters' => ['subselect1expr1'=>10, 'expr1'=>20],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT [a].*, [b].* FROM [a] INNER JOIN (SELECT [c].* FROM [c] WHERE [cc] = \'10\') AS [b] ON [d]=[e] WHERE [x] = \'20\'',
                        'prepare'    => 'SELECT [a].*, [b].* FROM [a] INNER JOIN (SELECT [c].* FROM [c] WHERE [cc] = ?) AS [b] ON [d]=[e] WHERE [x] = ?',
                        'parameters' => ['subselect1expr1'=>10, 'expr1'=>20],
                    ],
                ],
            ],
            'Ddl::CreateTable::processColumns()' => [
                'sqlObject' => $this->createTable('foo')
                                    ->addColumn($this->createColumn('col1')->setOption('identity', true)->setOption('comment', 'Comment1'))
                                    ->addColumn($this->createColumn('col2')->setOption('identity', true)->setOption('comment', 'Comment2')),
                'expected'  => [
                    'sql92'     => "CREATE TABLE \"foo\" ( \n    \"col1\" INTEGER NOT NULL,\n    \"col2\" INTEGER NOT NULL \n)",
                    'MySql'     => "CREATE TABLE `foo` ( \n    `col1` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Comment1',\n    `col2` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Comment2' \n)",
                    'Oracle'    => "CREATE TABLE \"foo\" ( \n    \"col1\" INTEGER NOT NULL,\n    \"col2\" INTEGER NOT NULL \n)",
                    'SqlServer' => "CREATE TABLE [foo] ( \n    [col1] INTEGER NOT NULL,\n    [col2] INTEGER NOT NULL \n)",
                ],
            ],
            'Ddl::CreateTable::processTable()' => [
                'sqlObject' => $this->createTable('foo')->setTemporary(true),
                'expected'  => [
                    'sql92'     => "CREATE TEMPORARY TABLE \"foo\" ( \n)",
                    'MySql'     => "CREATE TEMPORARY TABLE `foo` ( \n)",
                    'Oracle'    => "CREATE TEMPORARY TABLE \"foo\" ( \n)",
                    'SqlServer' => "CREATE TABLE [#foo] ( \n)",
                ],
            ],
            'Select::processSubSelect()' => [
                'sqlObject' => $this->select(['a' => $this->select(['b' => $this->select('c')->where(['cc'=>'CC'])])->where(['bb'=>'BB'])])->where(['aa'=>'AA']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "a".* FROM (SELECT "b".* FROM (SELECT "c".* FROM "c" WHERE "cc" = \'CC\') AS "b" WHERE "bb" = \'BB\') AS "a" WHERE "aa" = \'AA\'',
                        'prepare'    => 'SELECT "a".* FROM (SELECT "b".* FROM (SELECT "c".* FROM "c" WHERE "cc" = ?) AS "b" WHERE "bb" = ?) AS "a" WHERE "aa" = ?',
                        'parameters' => ['subselect2expr1' => 'CC', 'subselect1expr1' => 'BB', 'expr1' => 'AA'],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `a`.* FROM (SELECT `b`.* FROM (SELECT `c`.* FROM `c` WHERE `cc` = \'CC\') AS `b` WHERE `bb` = \'BB\') AS `a` WHERE `aa` = \'AA\'',
                        'prepare'    => 'SELECT `a`.* FROM (SELECT `b`.* FROM (SELECT `c`.* FROM `c` WHERE `cc` = ?) AS `b` WHERE `bb` = ?) AS `a` WHERE `aa` = ?',
                        'parameters' => ['subselect2expr1' => 'CC', 'subselect1expr1' => 'BB', 'expr1' => 'AA'],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT "a".* FROM (SELECT "b".* FROM (SELECT "c".* FROM "c" WHERE "cc" = \'CC\') "b" WHERE "bb" = \'BB\') "a" WHERE "aa" = \'AA\'',
                        'prepare'    => 'SELECT "a".* FROM (SELECT "b".* FROM (SELECT "c".* FROM "c" WHERE "cc" = ?) "b" WHERE "bb" = ?) "a" WHERE "aa" = ?',
                        'parameters' => ['subselect2expr1' => 'CC', 'subselect1expr1' => 'BB', 'expr1' => 'AA'],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT [a].* FROM (SELECT [b].* FROM (SELECT [c].* FROM [c] WHERE [cc] = \'CC\') AS [b] WHERE [bb] = \'BB\') AS [a] WHERE [aa] = \'AA\'',
                        'prepare'    => 'SELECT [a].* FROM (SELECT [b].* FROM (SELECT [c].* FROM [c] WHERE [cc] = ?) AS [b] WHERE [bb] = ?) AS [a] WHERE [aa] = ?',
                        'parameters' => ['subselect2expr1' => 'CC', 'subselect1expr1' => 'BB', 'expr1' => 'AA'],
                    ],
                ],
            ],
            'Delete::processSubSelect()' => [
                'sqlObject' => $this->delete('foo')->where(['x'=>$this->select('foo')->where(['x'=>'y'])]),
                'expected'  => [
                    'sql92'     => [
                        'string'     => 'DELETE FROM "foo" WHERE "x" = (SELECT "foo".* FROM "foo" WHERE "x" = \'y\')',
                        'prepare'    => 'DELETE FROM "foo" WHERE "x" = (SELECT "foo".* FROM "foo" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'MySql'     => [
                        'string'     => 'DELETE FROM `foo` WHERE `x` = (SELECT `foo`.* FROM `foo` WHERE `x` = \'y\')',
                        'prepare'    => 'DELETE FROM `foo` WHERE `x` = (SELECT `foo`.* FROM `foo` WHERE `x` = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'Oracle'    => [
                        'string'     => 'DELETE FROM "foo" WHERE "x" = (SELECT "foo".* FROM "foo" WHERE "x" = \'y\')',
                        'prepare'    => 'DELETE FROM "foo" WHERE "x" = (SELECT "foo".* FROM "foo" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'SqlServer' => [
                        'string'     => 'DELETE FROM [foo] WHERE [x] = (SELECT [foo].* FROM [foo] WHERE [x] = \'y\')',
                        'prepare'    => 'DELETE FROM [foo] WHERE [x] = (SELECT [foo].* FROM [foo] WHERE [x] = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                ],
            ],
            'Update::processSubSelect()' => [
                'sqlObject' => $this->update('foo')->set(['x'=>$this->select('foo')]),
                'expected'  => [
                    'sql92'     => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo")',
                    'MySql'     => 'UPDATE `foo` SET `x` = (SELECT `foo`.* FROM `foo`)',
                    'Oracle'    => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo")',
                    'SqlServer' => 'UPDATE [foo] SET [x] = (SELECT [foo].* FROM [foo])',
                ],
            ],
            'Insert::processSubSelect()' => [
                'sqlObject' => $this->insert('foo')->select($this->select('foo')->where(['x'=>'y'])),
                'expected'  => [
                    'sql92'     => [
                        'string'     => 'INSERT INTO "foo"  SELECT "foo".* FROM "foo" WHERE "x" = \'y\'',
                        'prepare'    => 'INSERT INTO "foo"  SELECT "foo".* FROM "foo" WHERE "x" = ?',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'MySql'     => [
                        'string'     => 'INSERT INTO `foo`  SELECT `foo`.* FROM `foo` WHERE `x` = \'y\'',
                        'prepare'    => 'INSERT INTO `foo`  SELECT `foo`.* FROM `foo` WHERE `x` = ?',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'Oracle'    => [
                        'string'     => 'INSERT INTO "foo"  SELECT "foo".* FROM "foo" WHERE "x" = \'y\'',
                        'prepare'    => 'INSERT INTO "foo"  SELECT "foo".* FROM "foo" WHERE "x" = ?',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'SqlServer' => [
                        'string'     => 'INSERT INTO [foo]  SELECT [foo].* FROM [foo] WHERE [x] = \'y\'',
                        'prepare'    => 'INSERT INTO [foo]  SELECT [foo].* FROM [foo] WHERE [x] = ?',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                ],
            ],
            'Update::processExpression()' => [
                'sqlObject' => $this->update('foo')->set(['x'=>new Sql\Expression('?', [$this->select('foo')->where(['x'=>'y'])])]),
                'expected'  => [
                    'sql92'     => [
                        'string'     => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo" WHERE "x" = \'y\')',
                        'prepare'    => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'MySql'     => [
                        'string'     => 'UPDATE `foo` SET `x` = (SELECT `foo`.* FROM `foo` WHERE `x` = \'y\')',
                        'prepare'    => 'UPDATE `foo` SET `x` = (SELECT `foo`.* FROM `foo` WHERE `x` = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'Oracle'    => [
                        'string'     => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo" WHERE "x" = \'y\')',
                        'prepare'    => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'SqlServer' => [
                        'string'     => 'UPDATE [foo] SET [x] = (SELECT [foo].* FROM [foo] WHERE [x] = \'y\')',
                        'prepare'    => 'UPDATE [foo] SET [x] = (SELECT [foo].* FROM [foo] WHERE [x] = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                ],
            ],
            'Update::processJoins()_1' => [
                'sqlObject' => $this->update('foo')->set(['x' => 'y'])->where(['xx' => 'yy'])->join(
                    'bar',
                    'bar.barId = foo.barId'
                ),
                'expected'  => [
                    'sql92'     => [
                        'string' => 'UPDATE "foo" INNER JOIN "bar" ON "bar"."barId" = "foo"."barId" SET "x" = \'y\' WHERE "xx" = \'yy\'',
                    ],
                    'MySql'     => [
                        'string' => 'UPDATE `foo` INNER JOIN `bar` ON `bar`.`barId` = `foo`.`barId` SET `x` = \'y\' WHERE `xx` = \'yy\'',
                    ],
                    'Oracle'     => [
                        'string' => 'UPDATE "foo" INNER JOIN "bar" ON "bar"."barId" = "foo"."barId" SET "x" = \'y\' WHERE "xx" = \'yy\'',
                    ],
                    'SqlServer' => [
                        'string' => 'UPDATE [foo] INNER JOIN [bar] ON [bar].[barId] = [foo].[barId] SET [x] = \'y\' WHERE [xx] = \'yy\'',
                    ],
                ],
            ],
            'Update::processJoins()_2' => [
                'sqlObject' => $this->update('Document')->set(['x' => 'y'])
                                    ->join(
                                        'User', // table name
                                        'User.UserId = Document.UserId' // expression to join on (will be quoted by platform object before insertion),
                                        // default JOIN INNER
                                    )
                                    ->join(
                                        'Category',
                                        'Category.CategoryId = Document.CategoryId',
                                        Sql\Join::JOIN_LEFT // (optional), one of inner, outer, left, right
                                    ),
                'expected'  => [
                    'sql92'     => [
                        'string' => 'UPDATE "Document" INNER JOIN "User" ON "User"."UserId" = "Document"."UserId" LEFT JOIN "Category" ON "Category"."CategoryId" = "Document"."CategoryId" SET "x" = \'y\'',
                    ],
                    'MySql'     => [
                        'string' => 'UPDATE `Document` INNER JOIN `User` ON `User`.`UserId` = `Document`.`UserId` LEFT JOIN `Category` ON `Category`.`CategoryId` = `Document`.`CategoryId` SET `x` = \'y\'',
                    ],
                    'Oracle'     => [
                        'string' => 'UPDATE "Document" INNER JOIN "User" ON "User"."UserId" = "Document"."UserId" LEFT JOIN "Category" ON "Category"."CategoryId" = "Document"."CategoryId" SET "x" = \'y\'',
                    ],
                    'SqlServer' => [
                        'string' => 'UPDATE [Document] INNER JOIN [User] ON [User].[UserId] = [Document].[UserId] LEFT JOIN [Category] ON [Category].[CategoryId] = [Document].[CategoryId] SET [x] = \'y\'',
                    ],
                ],
            ],
        ];
    }

    protected function dataProvider_Builders()
    {
        return [
            'RootDecorators::Select' => [
                'sqlObject' => $this->select('foo')->where(['x'=>$this->select('bar')]),
                'expected'  => [
                    'sql92'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Select' => 'ZendTest\Db\TestAsset\SelectBuilder',
                        ],
                        'string' => 'SELECT "foo".* FROM "foo" WHERE "x" = (SELECT "bar".* FROM "bar")',
                    ],
                    'MySql'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Select' => 'ZendTest\Db\TestAsset\SelectBuilder',
                        ],
                        'string' => 'SELECT `foo`.* FROM `foo` WHERE `x` = (SELECT `bar`.* FROM `bar`)',
                    ],
                    'Oracle'    => [
                        'decorators' => [
                            'Zend\Db\Sql\Select' => 'ZendTest\Db\TestAsset\SelectBuilder',
                        ],
                        'string' => 'SELECT "foo".* FROM "foo" WHERE "x" = (SELECT "bar".* FROM "bar")',
                    ],
                    'SqlServer' => [
                        'decorators' => [
                            'Zend\Db\Sql\Select' => 'ZendTest\Db\TestAsset\SelectBuilder',
                        ],
                        'string' => 'SELECT [foo].* FROM [foo] WHERE [x] = (SELECT [bar].* FROM [bar])',
                    ],
                ],
            ],
            'RootDecorators::Insert' => [
                'sqlObject' => $this->insert('foo')->select($this->select()),
                'expected'  => [
                    'sql92'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Insert' => 'ZendTest\Db\TestAsset\InsertBuilder', // Decorator for root sqlObject
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_Sql92=}']
                        ],
                        'string' => 'INSERT INTO "foo"  {=SELECT_Sql92=}',
                    ],
                    'MySql'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Insert' => 'ZendTest\Db\TestAsset\InsertBuilder', // Decorator for root sqlObject
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_MySql=}']
                        ],
                        'string' => 'INSERT INTO `foo`  {=SELECT_MySql=}',
                    ],
                    'Oracle'    => [
                        'decorators' => [
                            'Zend\Db\Sql\Insert' => 'ZendTest\Db\TestAsset\InsertBuilder', // Decorator for root sqlObject
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_Oracle=}']
                        ],
                        'string' => 'INSERT INTO "foo"  {=SELECT_Oracle=}',
                    ],
                    'SqlServer' => [
                        'decorators' => [
                            'Zend\Db\Sql\Insert' => 'ZendTest\Db\TestAsset\InsertBuilder', // Decorator for root sqlObject
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_SqlServer=}']
                        ],
                        'string' => 'INSERT INTO [foo]  {=SELECT_SqlServer=}',
                    ],
                ],
            ],
            'RootDecorators::Delete' => [
                'sqlObject' => $this->delete('foo')->where(['x'=>$this->select('foo')]),
                'expected'  => [
                    'sql92'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Delete' => 'ZendTest\Db\TestAsset\DeleteBuilder',
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_Sql92=}']
                        ],
                        'string' => 'DELETE FROM "foo" WHERE "x" = ({=SELECT_Sql92=})',
                    ],
                    'MySql'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Delete' => 'ZendTest\Db\TestAsset\DeleteBuilder',
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_MySql=}']
                        ],
                        'string' => 'DELETE FROM `foo` WHERE `x` = ({=SELECT_MySql=})',
                    ],
                    'Oracle'    => [
                        'decorators' => [
                            'Zend\Db\Sql\Delete' => 'ZendTest\Db\TestAsset\DeleteBuilder',
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_Oracle=}']
                        ],
                        'string' => 'DELETE FROM "foo" WHERE "x" = ({=SELECT_Oracle=})',
                    ],
                    'SqlServer' => [
                        'decorators' => [
                            'Zend\Db\Sql\Delete' => 'ZendTest\Db\TestAsset\DeleteBuilder',
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_SqlServer=}']
                        ],
                        'string' => 'DELETE FROM [foo] WHERE [x] = ({=SELECT_SqlServer=})',
                    ],
                ],
            ],
            'RootDecorators::Update' => [
                'sqlObject' => $this->update('foo')->where(['x'=>$this->select('foo')]),
                'expected'  => [
                    'sql92'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Update' => 'ZendTest\Db\TestAsset\UpdateBuilder',
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\Mysql\SelectBuilder', '{=SELECT_Sql92=}']
                        ],
                        'string' => 'UPDATE "foo" SET  WHERE "x" = ({=SELECT_Sql92=})',
                    ],
                    'MySql'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Update' => 'ZendTest\Db\TestAsset\UpdateBuilder',
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\Mysql\SelectBuilder', '{=SELECT_MySql=}']
                        ],
                        'string' => 'UPDATE `foo` SET  WHERE `x` = ({=SELECT_MySql=})',
                    ],
                    'Oracle'    => [
                        'decorators' => [
                            'Zend\Db\Sql\Update' => 'ZendTest\Db\TestAsset\UpdateBuilder',
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\Oracle\SelectBuilder', '{=SELECT_Oracle=}']
                        ],
                        'string' => 'UPDATE "foo" SET  WHERE "x" = ({=SELECT_Oracle=})',
                    ],
                    'SqlServer' => [
                        'decorators' => [
                            'Zend\Db\Sql\Update' => 'ZendTest\Db\TestAsset\UpdateBuilder',
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\SqlServer\SelectBuilder', '{=SELECT_SqlServer=}']
                        ],
                        'string' => 'UPDATE [foo] SET  WHERE [x] = ({=SELECT_SqlServer=})',
                    ],
                ],
            ],
            'DecorableExpression()' => [
                'sqlObject' => $this->update('foo')->where(['x'=>$this->expression('?', [$this->select('foo')])]),
                'expected'  => [
                    'sql92'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Expression' => 'ZendTest\Db\TestAsset\ExpressionBuilder',
                            'Zend\Db\Sql\Select'     => ['Zend\Db\Sql\Builder\Mysql\SelectBuilder', '{=SELECT_Sql92=}']
                        ],
                        'string'     => 'UPDATE "foo" SET  WHERE "x" = {decorate-({=SELECT_Sql92=})-decorate}',
                    ],
                    'MySql'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Expression' => 'ZendTest\Db\TestAsset\ExpressionBuilder',
                            'Zend\Db\Sql\Select'     => ['Zend\Db\Sql\Builder\Mysql\SelectBuilder', '{=SELECT_MySql=}']
                        ],
                        'string'     => 'UPDATE `foo` SET  WHERE `x` = {decorate-({=SELECT_MySql=})-decorate}',
                    ],
                    'Oracle'    => [
                        'decorators' => [
                            'Zend\Db\Sql\Expression' => 'ZendTest\Db\TestAsset\ExpressionBuilder',
                            'Zend\Db\Sql\Select'     => ['Zend\Db\Sql\Builder\Oracle\SelectBuilder', '{=SELECT_Oracle=}']
                        ],
                        'string'     => 'UPDATE "foo" SET  WHERE "x" = {decorate-({=SELECT_Oracle=})-decorate}',
                    ],
                    'SqlServer' => [
                        'decorators' => [
                            'Zend\Db\Sql\Expression' => 'ZendTest\Db\TestAsset\ExpressionBuilder',
                            'Zend\Db\Sql\Select'     => ['Zend\Db\Sql\Builder\SqlServer\SelectBuilder', '{=SELECT_SqlServer=}']
                        ],
                        'string'     => 'UPDATE [foo] SET  WHERE [x] = {decorate-({=SELECT_SqlServer=})-decorate}',
                    ],
                ],
            ],
        ];
    }
}
