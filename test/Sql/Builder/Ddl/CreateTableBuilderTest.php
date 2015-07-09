<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Ddl;

use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Ddl\Constraint;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

/**
 * @covers Zend\Db\Sql\Builder\sql92\Ddl\CreateTableBuilder
 * @covers Zend\Db\Sql\Builder\SqlServer\Ddl\CreateTableBuilder
 */
class CreateTableBuilderTest extends AbstractTestCase
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
            $this->dataProvider_ColumnsAndConstraint(),
            $this->dataProvider_Table()
        );
    }

    public function dataProvider_ColumnsAndConstraint()
    {
        return [
            'with options' => [
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
            'with options 2' => [
                'sqlObject' => function () {
                    $col = new Column('bar');
                    $col->setOption('zerofill', true);
                    $col->setOption('unsigned', true);
                    $col->setOption('identity', true);
                    $col->setOption('column-format', 'FIXED');
                    $col->setOption('storage', 'memory');
                    $col->setOption('comment', 'baz');
                    $col->addConstraint(new Constraint\PrimaryKey());

                    return $this->createTable('foo')->addColumn($col);
                },
                'expected'  => [
                    'mysql' => "CREATE TABLE `foo` ( \n    `bar` INTEGER UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'baz' COLUMN_FORMAT FIXED STORAGE MEMORY \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo')->addColumn(new Column('bar')),
                'expected'  => [
                    'sql92'     => "CREATE TABLE \"foo\" ( \n    \"bar\" INTEGER NOT NULL \n)",
                    'SqlServer' => "CREATE TABLE [foo] ( \n    [bar] INTEGER NOT NULL \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo', true)->addColumn(new Column('bar')),
                'expected'  => [
                    'sql92'     => "CREATE TEMPORARY TABLE \"foo\" ( \n    \"bar\" INTEGER NOT NULL \n)",
                    'SqlServer' => "CREATE TABLE [#foo] ( \n    [bar] INTEGER NOT NULL \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo', true)->addColumn(new Column('bar'))->addColumn(new Column('baz')),
                'expected'  => [
                    'sql92' => "CREATE TEMPORARY TABLE \"foo\" ( \n    \"bar\" INTEGER NOT NULL,\n    \"baz\" INTEGER NOT NULL \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo')->addColumn(new Column('bar'))->addConstraint(new Constraint\PrimaryKey('bat')),
                'expected'  => [
                    'sql92' => "CREATE TABLE \"foo\" ( \n    \"bar\" INTEGER NOT NULL , \n    PRIMARY KEY (\"bat\") \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo')->addConstraint(new Constraint\PrimaryKey('bar'))->addConstraint(new Constraint\PrimaryKey('bat')),
                'expected'  => [
                    'sql92' => "CREATE TABLE \"foo\" ( \n    PRIMARY KEY (\"bar\"),\n    PRIMARY KEY (\"bat\") \n)",
                ],
            ],
        ];
    }

    public function dataProvider_Table()
    {
        return [
            'not temporary' => [
                'sqlObject' => $this->createTable('foo'),
                'expected'  => [
                    'sql92' => "CREATE TABLE \"foo\" ( \n)",
                    'SqlServer' => "CREATE TABLE [foo] ( \n)",
                ],
            ],
            'temporary' => [
                'sqlObject' => $this->createTable('foo')->setTemporary(true),
                'expected'  => [
                    'sql92'     => "CREATE TEMPORARY TABLE \"foo\" ( \n)",
                    'MySql'     => "CREATE TEMPORARY TABLE `foo` ( \n)",
                    'Oracle'    => "CREATE TEMPORARY TABLE \"foo\" ( \n)",
                    'SqlServer' => "CREATE TABLE [#foo] ( \n)",
                ],
            ],
            'temporary via constructor' => [
                'sqlObject' => $this->createTable('foo', true),
                'expected'  => [
                    'sql92' => "CREATE TEMPORARY TABLE \"foo\" ( \n)",
                    'SqlServer' => "CREATE TABLE [#foo] ( \n)",
                ],
            ],
        ];
    }
}
