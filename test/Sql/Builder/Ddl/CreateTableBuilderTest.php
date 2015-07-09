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
            $this->dataProvider_SQL92(),
            $this->dataProvider_Mysql(),
            $this->dataProvider_SqlServer()
        );
    }

    public function dataProvider_SqlServer()
    {
        return [
            [
                'sqlObject' => $this->createTable('foo'),
                'expected'  => [
                    'SqlServer' => "CREATE TABLE [foo] ( \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo', true),
                'expected'  => [
                    'SqlServer' => "CREATE TABLE [#foo] ( \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo')->addColumn(new Column('bar')),
                'expected'  => [
                    'SqlServer' => "CREATE TABLE [foo] ( \n    [bar] INTEGER NOT NULL \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo', true)->addColumn(new Column('bar')),
                'expected'  => [
                    'SqlServer' => "CREATE TABLE [#foo] ( \n    [bar] INTEGER NOT NULL \n)",
                ],
            ],
        ];
    }

    public function dataProvider_Mysql()
    {
        return [
            [
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
        ];
    }

    public function dataProvider_SQL92()
    {
        return [
            [
                'sqlObject' => $this->createTable('foo'),
                'expected'  => [
                    'sql92' => "CREATE TABLE \"foo\" ( \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo', true),
                'expected'  => [
                    'sql92' => "CREATE TEMPORARY TABLE \"foo\" ( \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo')->addColumn(new Column('bar')),
                'expected'  => [
                    'sql92' => "CREATE TABLE \"foo\" ( \n    \"bar\" INTEGER NOT NULL \n)",
                ],
            ],
            [
                'sqlObject' => $this->createTable('foo', true)->addColumn(new Column('bar')),
                'expected'  => [
                    'sql92' => "CREATE TEMPORARY TABLE \"foo\" ( \n    \"bar\" INTEGER NOT NULL \n)",
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
}
