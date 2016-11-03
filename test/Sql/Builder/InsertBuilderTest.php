<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\TableIdentifier;

/**
 * @covers Zend\Db\Sql\Builder\sql92\InsertBuilder
 */
class InsertBuilderTest extends AbstractTestCase
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
            $this->dataProvider_Into(),
            $this->dataProvider_ColumnsAndValues()
        );
    }

    public function dataProvider_Into()
    {
        return [
            'into_TableIdentifier' =>  [
                'sqlObject' => $this->insert()->into(new TableIdentifier('foo', 'schema'))->values(['c1' => 'v1']),
                'expected'  => [
                    'sql92' => 'INSERT INTO "schema"."foo" ("c1") VALUES (\'v1\')',
                ],
            ],
            'into_string' =>  [
                'sqlObject' => $this->insert()->into('foo')->values(['c1' => 'v1']),
                'expected'  => [
                    'sql92' => 'INSERT INTO "foo" ("c1") VALUES (\'v1\')',
                ],
            ],
            'into_array' =>  [
                'sqlObject' => $this->insert()->into(['schema', 'foo'])->values(['c1' => 'v1']),
                'expected'  => [
                    'sql92' => 'INSERT INTO "schema"."foo" ("c1") VALUES (\'v1\')',
                ],
            ],
        ];
    }

    public function dataProvider_ColumnsAndValues()
    {
        return [
            'columns_in_values' => [
                'sqlObject' => $this->insert('foo')
                                        ->values([
                                            'bar' => 'baz',
                                            'boo' => new Expression('NOW()'),
                                            'bam' => null,
                                            'bat' => $this->select('bad')
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'INSERT INTO "foo" ("bar", "boo", "bam", "bat") VALUES (\'baz\', NOW(), NULL, (SELECT "bad".* FROM "bad"))',
                        'prepare' => 'INSERT INTO "foo" ("bar", "boo", "bam", "bat") VALUES (?, NOW(), ?, (SELECT "bad".* FROM "bad"))',
                        'parameters' => ['bar' => 'baz', 'bam' => null],
                    ],
                ],
            ],
            'values with merge' => [
                'sqlObject' => $this->insert('foo')
                                        ->values([
                                            'bar' => 'baz',
                                            'boo' => new Expression('NOW()'),
                                            'bam' => null
                                        ])
                                        ->values(['qux' => 100], Insert::VALUES_MERGE),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'INSERT INTO "foo" ("bar", "boo", "bam", "qux") VALUES (\'baz\', NOW(), NULL, \'100\')',
                    ],
                ],
            ],
            'row_with_columns' => [
                'sqlObject' => $this->insert('foo')
                                        ->columns(['c1'])
                                        ->values(['v1']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'INSERT INTO "foo" ("c1") VALUES (\'v1\')',
                        'prepare'    => 'INSERT INTO "foo" ("c1") VALUES (?)',
                        'parameters' => ['c1' => 'v1'],
                    ],
                ],
            ],
            'row_without_columns' => [
                'sqlObject' => $this->insert('foo')
                                        ->values(['v1']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'INSERT INTO "foo" VALUES (\'v1\')',
                        'prepare'    => 'INSERT INTO "foo" VALUES (\'v1\')',
                        'parameters' => [],
                    ],
                ],
            ],
            'select_with_columns' => [
                'sqlObject' => $this->insert('foo')
                                        ->columns(['col1', 'col2'])
                                        ->select($this->select()->from('bar')->where(['x'=>5])),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'INSERT INTO "foo" ("col1", "col2") (SELECT "bar".* FROM "bar" WHERE "x" = \'5\')',
                        'prepare' => 'INSERT INTO "foo" ("col1", "col2") (SELECT "bar".* FROM "bar" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1'=>5],
                    ],
                    'MySql'     => [
                        'string'     => 'INSERT INTO `foo` (`col1`, `col2`) (SELECT `bar`.* FROM `bar` WHERE `x` = \'5\')',
                        'prepare'    => 'INSERT INTO `foo` (`col1`, `col2`) (SELECT `bar`.* FROM `bar` WHERE `x` = ?)',
                        'parameters' => ['subselect1expr1' => 5],
                    ],
                    'Oracle'    => [
                        'string'     => 'INSERT INTO "foo" ("col1", "col2") (SELECT "bar".* FROM "bar" WHERE "x" = \'5\')',
                        'prepare'    => 'INSERT INTO "foo" ("col1", "col2") (SELECT "bar".* FROM "bar" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 5],
                    ],
                    'SqlServer' => [
                        'string'     => 'INSERT INTO [foo] ([col1], [col2]) (SELECT [bar].* FROM [bar] WHERE [x] = \'5\')',
                        'prepare'    => 'INSERT INTO [foo] ([col1], [col2]) (SELECT [bar].* FROM [bar] WHERE [x] = ?)',
                        'parameters' => ['subselect1expr1' => 5],
                    ],
                ],
            ],
            'select_without_columns' => [
                'sqlObject' => $this->insert('foo')
                                        ->select($this->select('bar')->where(['x'=>5])),
                'expected'  => [
                    'sql92'     => [
                        'string'     => 'INSERT INTO "foo" (SELECT "bar".* FROM "bar" WHERE "x" = \'5\')',
                        'prepare'    => 'INSERT INTO "foo" (SELECT "bar".* FROM "bar" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 5],
                    ],
                    'MySql'     => [
                        'string'     => 'INSERT INTO `foo` (SELECT `bar`.* FROM `bar` WHERE `x` = \'5\')',
                        'prepare'    => 'INSERT INTO `foo` (SELECT `bar`.* FROM `bar` WHERE `x` = ?)',
                        'parameters' => ['subselect1expr1' => 5],
                    ],
                    'Oracle'    => [
                        'string'     => 'INSERT INTO "foo" (SELECT "bar".* FROM "bar" WHERE "x" = \'5\')',
                        'prepare'    => 'INSERT INTO "foo" (SELECT "bar".* FROM "bar" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 5],
                    ],
                    'SqlServer' => [
                        'string'     => 'INSERT INTO [foo] (SELECT [bar].* FROM [bar] WHERE [x] = \'5\')',
                        'prepare'    => 'INSERT INTO [foo] (SELECT [bar].* FROM [bar] WHERE [x] = ?)',
                        'parameters' => ['subselect1expr1' => 5],
                    ],
                ],
            ],
            'select_with_combine_in_select' => [
                'sqlObject' => $this->insert('foo')
                                        ->select($this->combine($this->select('bar'))),
                'expected'  => [
                    'sql92'     => 'INSERT INTO "foo" ((SELECT "bar".* FROM "bar"))',
                    'MySql'     => 'INSERT INTO `foo` ((SELECT `bar`.* FROM `bar`))',
                    'Oracle'    => 'INSERT INTO "foo" ((SELECT "bar".* FROM "bar"))',
                    'SqlServer' => 'INSERT INTO [foo] ((SELECT [bar].* FROM [bar]))',
                ],
            ],
            'multiple_row_with_columns' => [
                'sqlObject' => $this->insert()
                                        ->into('foo')
                                        ->columns(['c1', 'c2'])
                                        ->values([
                                            ['1_v1', '1_v2'],
                                            ['2_v1', '2_v2'],
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'INSERT INTO "foo" ("c1", "c2") VALUES (\'1_v1\', \'1_v2\'), (\'2_v1\', \'2_v2\')',
                        'prepare'    => 'INSERT INTO "foo" ("c1", "c2") VALUES (\'1_v1\', \'1_v2\'), (\'2_v1\', \'2_v2\')',
                        'parameters' => [],
                    ],
                ],
            ],
            'multiple_row_without_columns' => [
                'sqlObject' => $this->insert()
                                        ->into('foo')
                                        ->values([
                                            ['1_v1', '1_v2'],
                                            ['2_v1', '2_v2'],
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'INSERT INTO "foo" VALUES (\'1_v1\', \'1_v2\'), (\'2_v1\', \'2_v2\')',
                        'prepare'    => 'INSERT INTO "foo" VALUES (\'1_v1\', \'1_v2\'), (\'2_v1\', \'2_v2\')',
                        'parameters' => [],
                    ],
                ],
            ],
            'multiple_row_with_different_values_types' => [
                'sqlObject' => $this->insert()->into('foo')
                                        ->values([
                                            [
                                                'v1',
                                                'v2',
                                                'v3',
                                                'v4',
                                            ],
                                            [
                                                'bar',
                                                null,
                                                new Expression('NOW()'),
                                                $this->select('baz'),
                                            ],
                                            [
                                                'v5',
                                                'v6',
                                                'v7',
                                                'v8',
                                            ],
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'INSERT INTO "foo" VALUES (\'v1\', \'v2\', \'v3\', \'v4\'), (\'bar\', NULL, NOW(), (SELECT "baz".* FROM "baz")), (\'v5\', \'v6\', \'v7\', \'v8\')',
                        'string'  => 'INSERT INTO "foo" VALUES (\'v1\', \'v2\', \'v3\', \'v4\'), (\'bar\', NULL, NOW(), (SELECT "baz".* FROM "baz")), (\'v5\', \'v6\', \'v7\', \'v8\')',
                        'parameters' => [],
                    ],
                ],
            ],
        ];
    }
}
