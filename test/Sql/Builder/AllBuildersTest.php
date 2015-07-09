<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

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
        return $this->prepareDataProvider([
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
                        'string' => 'INSERT INTO "foo" ({=SELECT_Sql92=})',
                    ],
                    'MySql'     => [
                        'decorators' => [
                            'Zend\Db\Sql\Insert' => 'ZendTest\Db\TestAsset\InsertBuilder', // Decorator for root sqlObject
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_MySql=}']
                        ],
                        'string' => 'INSERT INTO `foo` ({=SELECT_MySql=})',
                    ],
                    'Oracle'    => [
                        'decorators' => [
                            'Zend\Db\Sql\Insert' => 'ZendTest\Db\TestAsset\InsertBuilder', // Decorator for root sqlObject
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_Oracle=}']
                        ],
                        'string' => 'INSERT INTO "foo" ({=SELECT_Oracle=})',
                    ],
                    'SqlServer' => [
                        'decorators' => [
                            'Zend\Db\Sql\Insert' => 'ZendTest\Db\TestAsset\InsertBuilder', // Decorator for root sqlObject
                            'Zend\Db\Sql\Select' => ['Zend\Db\Sql\Builder\sql92\SelectBuilder', '{=SELECT_SqlServer=}']
                        ],
                        'string' => 'INSERT INTO [foo] ({=SELECT_SqlServer=})',
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
            'DISTINCT in columns' => [
                'sqlObject' => $this->select('foo')->columns([$this->expression('DISTINCT(bar) as bar')])->limit(5)->offset(10),
                'expected'  => [
                    'SqlServer' => [
                        'string'  => 'SELECT DISTINCT(bar) as bar FROM ( SELECT DISTINCT(bar) as bar, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN 10+1 AND 5+10'
                    ],
                ],
            ],
        ]);
    }
}
