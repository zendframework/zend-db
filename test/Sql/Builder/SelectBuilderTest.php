<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\TableIdentifier;

/**
 * @covers Zend\Db\Sql\Builder\IbmDb2\SelectBuilder
 * @covers Zend\Db\Sql\Builder\Mysql\SelectBuilder
 * @covers Zend\Db\Sql\Builder\Oracle\SelectBuilder
 * @covers Zend\Db\Sql\Builder\SqlServer\SelectBuilder
 * @covers Zend\Db\Sql\Builder\sql92\SelectBuilder
 */
class SelectBuilderTest extends AbstractTestCase
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
            $this->dataProvider_Columns(),
            $this->dataProvider_Combine(),
            $this->dataProvider_GroupBy(),
            $this->dataProvider_Having(),
            $this->dataProvider_Join(),
            $this->dataProvider_LimitOffset(),
            $this->dataProvider_Order(),
            $this->dataProvider_Quantitifier(),
            $this->dataProvider_SubSelects(),
            $this->dataProvider_Table(),
            $this->dataProvider_Where()
        );
    }

    public function dataProvider_Columns()
    {
        return [
            [ // columns
                'sqlObject' => $this->select()->from('foo')->columns(['bar', 'baz']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo"."bar" AS "bar", "foo"."baz" AS "baz" FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // columns with AS associative array
                'sqlObject' => $this->select()->from('foo')->columns(['bar' => 'baz']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo"."baz" AS "bar" FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // columns with AS associative array mixed
                'sqlObject' => $this->select()->from('foo')->columns(['bar' => 'baz', 'bam']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo"."baz" AS "bar", "foo"."bam" AS "bam" FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // columns where value is Expression, with AS
                'sqlObject' => $this->select()->from('foo')->columns(['bar' => new Expression('COUNT(some_column)')]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT COUNT(some_column) AS "bar" FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // columns where value is Expression
                'sqlObject' => $this->select()->from('foo')->columns([new Expression('COUNT(some_column) AS bar')]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT COUNT(some_column) AS bar FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()
                                    ->from('foo')
                                    ->columns(
                                        [
                                            new Expression(
                                                '(COUNT(?) + ?) AS ?',
                                                [
                                                    ['some_column', Expression::TYPE_IDENTIFIER],
                                                    [5,             Expression::TYPE_VALUE],
                                                    ['bar',         Expression::TYPE_IDENTIFIER],
                                                ]
                                            )
                                        ]
                                    ),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT (COUNT("some_column") + \'5\') AS "bar" FROM "foo"',
                        'prepare' => 'SELECT (COUNT("some_column") + ?) AS "bar" FROM "foo"',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->from(['x' => 'foo'])->columns(['bar' => 'foo.bar'], false),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo"."bar" AS "bar" FROM "foo" AS "x"',
                        'prepare' => 'SELECT "foo"."bar" AS "bar" FROM "foo" AS "x"',
                    ],
                ],
            ],
            [ // @author robertbasic // @link https://github.com/zendframework/zf2/pull/2714
                'sqlObject' => $this->select()->from('foo')->columns(['bar'], false),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "bar" AS "bar" FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select('table')->columns(['*']),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "table".* FROM "table"',
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Combine()
    {
        return [
            [
                'sqlObject' => $this->select()
                                        ->from('foo')
                                        ->where('a = b')
                                        ->combine(
                                            $this->select()->from('bar')->where('c = d'),
                                            Select::COMBINE_UNION,
                                            'ALL'
                                        ),
                'expected'  => [
                    'sql92' => [
                        'string'  => '( SELECT "foo".* FROM "foo" WHERE a = b ) UNION ALL ( (SELECT "bar".* FROM "bar" WHERE c = d) )',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // combine and union with order at the end
                'sqlObject' => $this->select()
                                        ->from([
                                            'sub' => $this->select()
                                                        ->from('foo')
                                                        ->where('a = b')
                                                        ->combine(
                                                            $this->select()->from('bar')->where('c = d')
                                                        )
                                        ])
                                        ->order('id DESC'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "sub".* FROM (( SELECT "foo".* FROM "foo" WHERE a = b ) UNION ( (SELECT "bar".* FROM "bar" WHERE c = d) )) AS "sub" ORDER BY "id" DESC',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // Combine
                'sqlObject' => $this->select()
                                        ->from(['foo'=>$this->combine($this->select('bar0'))])
                                        ->columns(['c1'=>$this->combine($this->select('bar1'))])
                                        ->where(['c2'=>$this->combine($this->select('bar2'))])
                                        ->join(['c3'=>$this->combine($this->select('bar3'))], 'xx=yy'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT ((SELECT "bar1".* FROM "bar1")) AS "c1", "c3".* FROM ((SELECT "bar0".* FROM "bar0")) AS "foo" INNER JOIN ((SELECT "bar3".* FROM "bar3")) AS "c3" ON "xx"="yy" WHERE "c2" = ((SELECT "bar2".* FROM "bar2"))',
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_GroupBy()
    {
        return [
            [ // group
                'sqlObject' => $this->select()->from('foo')->group(['col1', 'col2']),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" GROUP BY "col1", "col2"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // group
                'sqlObject' => $this->select()->from('foo')->group('col1')->group('col2'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" GROUP BY "col1", "col2"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // group
                'sqlObject' => $this->select()->from('foo')->group(new Expression('DAY(?)', [['col1', Expression::TYPE_IDENTIFIER]])),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" GROUP BY DAY("col1")',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // group with compound name
                'sqlObject' => $this->select()->from('foo')->group('c1.d2'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" GROUP BY "c1"."d2"',
                        'prepare' => true,
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Having()
    {
        return [
            [ // having (simple string)
                'sqlObject' => $this->select()->from('foo')->having('x = 5'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" HAVING x = 5',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // having (returning parameters)
                'sqlObject' => $this->select()->from('foo')->having(['x = ?' => 5]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" HAVING x = \'5\'',
                        'prepare' => 'SELECT "foo".* FROM "foo" HAVING x = ?',
                        'parameters' => ['expr1' => 5],
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Join()
    {
        return [
            [ // joins (plain)
                'sqlObject' => $this->select()->from('foo')->join('zac', 'm = n'),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".*, "zac".* FROM "foo" INNER JOIN "zac" ON "m" = "n"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // join with columns
                'sqlObject' => $this->select()->from('foo')->join('zac', 'm = n', ['bar', 'baz']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".*, "zac"."bar" AS "bar", "zac"."baz" AS "baz" FROM "foo" INNER JOIN "zac" ON "m" = "n"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // join with alternate type
                'sqlObject' => $this->select()->from('foo')->join('zac', 'm = n', ['bar', 'baz'], Select::JOIN_OUTER),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".*, "zac"."bar" AS "bar", "zac"."baz" AS "baz" FROM "foo" OUTER JOIN "zac" ON "m" = "n"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // join with column aliases
                'sqlObject' => $this->select()->from('foo')->join('zac', 'm = n', ['BAR' => 'bar', 'BAZ' => 'baz']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".*, "zac"."bar" AS "BAR", "zac"."baz" AS "BAZ" FROM "foo" INNER JOIN "zac" ON "m" = "n"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // join with table aliases
                'sqlObject' => $this->select()->from('foo')->join(['b' => 'bar'], 'b.foo_id = foo.foo_id'),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".*, "b".* FROM "foo" INNER JOIN "bar" AS "b" ON "b"."foo_id" = "foo"."foo_id"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // joins with a few keywords in the on clause
                'sqlObject' => $this->select()->from('foo')->join('zac', '(m = n AND c.x) BETWEEN x AND y.z OR (c.x < y.z AND c.x <= y.z AND c.x > y.z AND c.x >= y.z)'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "zac".* FROM "foo" INNER JOIN "zac" ON ("m" = "n" AND "c"."x") BETWEEN "x" AND "y"."z" OR ("c"."x" < "y"."z" AND "c"."x" <= "y"."z" AND "c"."x" > "y"."z" AND "c"."x" >= "y"."z")',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // join with expression in ON part
                'sqlObject' => $this->select()->from('foo')->join('zac', new Expression('(m = n AND c.x) BETWEEN x AND y.z')),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "zac".* FROM "foo" INNER JOIN "zac" ON (m = n AND c.x) BETWEEN x AND y.z',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // join with Expression object in COLUMNS part (ZF2-514) // @co-author Koen Pieters (kpieters)
                'sqlObject' => $this->select()->from('foo')->columns([])->join('bar', 'm = n', ['thecount' => new Expression("COUNT(*)")]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT COUNT(*) AS "thecount" FROM "foo" INNER JOIN "bar" ON "m" = "n"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // multiple joins with expressions // reported by @jdolieslager
                'sqlObject' => $this->select()
                                    ->from('foo')
                                    ->join('tableA', new Predicate\Operator('id', '=', 1))
                                    ->join('tableB', new Predicate\Operator('id', '=', 2))
                                    ->join('tableC', new Predicate\PredicateSet([
                                        new Predicate\Operator('id', '=', 3),
                                        new Predicate\Operator('number', '>', 20)
                                    ])),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "tableA".*, "tableB".*, "tableC".* FROM "foo" '
                                        . 'INNER JOIN "tableA" ON "id" = \'1\' INNER JOIN "tableB" ON "id" = \'2\' '
                                        . 'INNER JOIN "tableC" ON "id" = \'3\' AND "number" > \'20\'',
                        'prepare' => 'SELECT "foo".*, "tableA".*, "tableB".*, "tableC".* FROM "foo"'
                                        . ' INNER JOIN "tableA" ON "id" = :expr1 INNER JOIN "tableB" ON "id" = :expr2 '
                                        . 'INNER JOIN "tableC" ON "id" = :expr3 AND "number" > :expr4',
                        'useNamedParams' => true,
                    ],
                ],
            ],
            [ //Expression as joinName
                'sqlObject' => $this->select()
                                        ->from(new TableIdentifier('foo'))
                                        ->join(['bar' => new Expression('psql_function_which_returns_table')], 'foo.id = bar.fooid'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "bar".* FROM "foo" INNER JOIN psql_function_which_returns_table AS "bar" ON "foo"."id" = "bar"."fooid"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // @author Andrzej Lewandowski @link https://github.com/zendframework/zf2/issues/7222
                'sqlObject' => $this->select()->from('foo')->join('zac', '(catalog_category_website.category_id = catalog_category.category_id)'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "zac".* FROM "foo" INNER JOIN "zac" ON ("catalog_category_website"."category_id" = "catalog_category"."category_id")',
                        'prepare' => true,
                    ],
                ],
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
        ];
    }

    public function dataProvider_LimitOffset()
    {
        return [
            'Offset0' => [
                'sqlObject' => $this->select('foo')->offset(0),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" OFFSET \'0\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" OFFSET ?',
                        'parameters' => ['offset' => 0],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 18446744073709551615 OFFSET 0',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT 18446744073709551615 OFFSET ?',
                        'parameters' => ['offset' => 0],
                    ],
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ?',
                        'parameters' => ['offset' => 0],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ?',
                        'parameters' => ['offset' => 0],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > ?',
                        'parameters' => ['offset' => 0],
                    ],
                ],
            ],
            'Limit0' => [
                'sqlObject' => $this->select()->from('foo')->limit(0),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" LIMIT \'0\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" LIMIT ?',
                        'parameters' => ['limit' => 0],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 0',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT ?',
                        'parameters' => ['limit' => 0],
                    ],
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= ?',
                        'parameters' => ['limit' => 0],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= ?',
                        'parameters' => ['limit' => 0],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] <= \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] <= ?',
                        'parameters' => ['limit' => 0],
                    ],
                ],
            ],
            'Offset10' => [
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
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ?',
                        'parameters' => ['offset' => 10],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ?',
                        'parameters' => ['offset' => 10],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > ?',
                        'parameters' => ['offset' => 10],
                    ],
                ],
            ],
            'Offset10_Limit0' => [
                'sqlObject' => $this->select('foo')->offset(10)->limit(0),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" LIMIT \'0\' OFFSET \'10\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => 0, 'offset' => 10],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 0 OFFSET 10',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => 0, 'offset' => 10],
                    ],
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'10\' AND "LIMIT_OFFSET_ROWNUM" <= \'0\' + \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ? AND "LIMIT_OFFSET_ROWNUM" <= ? + ?',
                        'parameters' => ['offset' => 10, 'limit' => 0, 'offsetForSum' => 10],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'10\' AND "LIMIT_OFFSET_ROWNUM" <= \'0\' + \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ? AND "LIMIT_OFFSET_ROWNUM" <= ? + ?',
                        'parameters' => ['offset' => 10, 'limit' => 0, 'offsetForSum' => 10],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > \'10\' AND [LIMIT_OFFSET_ROWNUM] <= \'0\' + \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > ? AND [LIMIT_OFFSET_ROWNUM] <= ? + ?',
                        'parameters' => ['offset' => 10, 'limit' => 0, 'offsetForSum' => 10],
                    ],
                ],
            ],
            'Limit10' => [
                'sqlObject' => $this->select()->from('foo')->limit(10),
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
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= ?',
                        'parameters' => ['limit' => 10],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= ?',
                        'parameters' => ['limit' => 10],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] <= \'10\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] <= ?',
                        'parameters' => ['limit' => 10],
                    ],
                ],
            ],
            'Limit10_Offset0' => [
                'sqlObject' => $this->select('foo')->offset(0)->limit(10),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" LIMIT \'10\' OFFSET \'0\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => 10, 'offset' => 0],
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 10 OFFSET 0',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => 10, 'offset' => 0],
                    ],
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'0\' AND "LIMIT_OFFSET_ROWNUM" <= \'10\' + \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ? AND "LIMIT_OFFSET_ROWNUM" <= ? + ?',
                        'parameters' => ['offset' => 0, 'limit' => 10, 'offsetForSum' => 0],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'0\' AND "LIMIT_OFFSET_ROWNUM" <= \'10\' + \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ? AND "LIMIT_OFFSET_ROWNUM" <= ? + ?',
                        'parameters' => ['offset' => 0, 'limit' => 10, 'offsetForSum' => 0],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > \'0\' AND [LIMIT_OFFSET_ROWNUM] <= \'10\' + \'0\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > ? AND [LIMIT_OFFSET_ROWNUM] <= ? + ?',
                        'parameters' => ['offset' => 0, 'limit' => 10, 'offsetForSum' => 0],
                    ],
                ],
            ],
            'Limit10_Offset5' => [
                'sqlObject' => $this->select('foo')->offset(5)->limit(10),
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
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'5\' AND "LIMIT_OFFSET_ROWNUM" <= \'10\' + \'5\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ? AND "LIMIT_OFFSET_ROWNUM" <= ? + ?',
                        'parameters' => ['offset' => 5, 'limit' => 10, 'offsetForSum' => 5],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > \'5\' AND "LIMIT_OFFSET_ROWNUM" <= \'10\' + \'5\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ? AND "LIMIT_OFFSET_ROWNUM" <= ? + ?',
                        'parameters' => ['offset' => 5, 'limit' => 10, 'offsetForSum' => 5],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > \'5\' AND [LIMIT_OFFSET_ROWNUM] <= \'10\' + \'5\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > ? AND [LIMIT_OFFSET_ROWNUM] <= ? + ?',
                        'parameters' => ['offset' => 5, 'limit' => 10, 'offsetForSum' => 5],
                    ],
                ],
            ],
            //==================================================================
            'Limit10_Offset5_WithStringValues' => [
                'sqlObject' => $this->select()->from('foo')->limit("5")->offset("10"),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" LIMIT \'5\' OFFSET \'10\'',
                        'prepare' => 'SELECT "foo".* FROM "foo" LIMIT ? OFFSET ?',
                        'parametersEquals' => ['limit' => 5, 'offset' => 10],
                    ],
                ],
            ],
            'Limit10_Offset5_WithBigStringValues' => [ // limit with big offset and limit
                'sqlObject' => $this->select()->from('foo')->limit("10000000000000000000")->offset("10000000000000000000"),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" LIMIT \'10000000000000000000\' OFFSET \'10000000000000000000\'',
                        'prepare' => 'SELECT "foo".* FROM "foo" LIMIT ? OFFSET ?',
                        'parametersEquals' => ['limit' => 10000000000000000000, 'offset' => 10000000000000000000],
                    ],
                    'Mysql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 10000000000000000000 OFFSET 10000000000000000000',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => '10000000000000000000', 'offset' => '10000000000000000000'],
                    ],
                    /* TODO
                    'IbmDb2' => array(
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > 5 AND "LIMIT_OFFSET_ROWNUM" <= 10 + 5',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ? AND "LIMIT_OFFSET_ROWNUM" <= ? + ?',
                        'parameters' => array('offset' => 5, 'limit' => 10, 'offsetForSum' => 5),
                    ),
                    'Oracle' => array(
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > 5 AND "LIMIT_OFFSET_ROWNUM" <= 10 + 5',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo") "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" > ? AND "LIMIT_OFFSET_ROWNUM" <= ? + ?',
                        'parameters' => array('offset' => 5, 'limit' => 10, 'offsetForSum' => 5),
                    ),
                    'SqlServer' => array(
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > 5 AND [LIMIT_OFFSET_ROWNUM] <= 10 + 5',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo]) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] > ? AND [LIMIT_OFFSET_ROWNUM] <= ? + ?',
                        'parameters' => array('offset' => 5, 'limit' => 10, 'offsetForSum' => 5),
                    ),*/
                ],
            ],
            'LimitOffset_ParametersOrder' => [
                'sqlObject' => $this->select()->from('foo')->where(['x' => 7])->limit(5),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" WHERE "x" = \'7\' LIMIT \'5\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" WHERE "x" = ? LIMIT ?',
                        //TODO 'parameters' => array('subselect2expr1' => 7, 'limit' => 5),
                    ],
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` WHERE `x` = \'7\' LIMIT 5',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` WHERE `x` = ? LIMIT ?',
                        //TODO 'parameters' => array('subselect2expr1' => 7, 'limit' => 5),
                    ],
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo" WHERE "x" = \'7\') "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= \'5\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo" WHERE "x" = ?) "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= ?',
                        'parameters' => ['subselect2expr1' => 7, 'limit' => 5],
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo" WHERE "x" = \'7\') "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= \'5\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS "LIMIT_OFFSET_ROWNUM" FROM (SELECT "foo".* FROM "foo" WHERE "x" = ?) "LIMIT_OFFSET_WRAP_1") "LIMIT_OFFSET_WRAP_2" WHERE "LIMIT_OFFSET_ROWNUM" <= ?',
                        'parameters' => ['subselect2expr1' => 7, 'limit' => 5],
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo] WHERE [x] = \'7\') AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] <= \'5\'',
                        'prepare'    => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER () AS [LIMIT_OFFSET_ROWNUM] FROM (SELECT [foo].* FROM [foo] WHERE [x] = ?) AS [LIMIT_OFFSET_WRAP_1]) AS [LIMIT_OFFSET_WRAP_2] WHERE [LIMIT_OFFSET_ROWNUM] <= ?',
                        'parameters' => ['subselect2expr1' => 7, 'limit' => 5],
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Order()
    {
        return [
            [ // order
                'sqlObject' => $this->select()->from('foo')->order('c1'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" ORDER BY "c1" ASC',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // order
                'sqlObject' => $this->select()->from('foo')->order(['c1', 'c2']),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" ORDER BY "c1" ASC, "c2" ASC',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // order - notice partially lower case ASC
                'sqlObject' => $this->select()->from('foo')->order(['c1' => 'DESC', 'c2' => 'Asc']),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" ORDER BY "c1" DESC, "c2" ASC',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // order
                'sqlObject' => $this->select()->from('foo')->order(['c1' => 'asc'])->order('c2 desc'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" ORDER BY "c1" ASC, "c2" DESC',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // order with compound name
                'sqlObject' => $this->select()->from('foo')->order('c1.d2'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" ORDER BY "c1"."d2" ASC',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // @author Demian Katz
                'sqlObject' => $this->select()
                                    ->from('table')
                                    ->order([
                                        new Expression('isnull(?) DESC', [['name', Expression::TYPE_IDENTIFIER]]),
                                        'name'
                                    ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "table".* FROM "table" ORDER BY isnull("name") DESC, "name" ASC',
                        'prepare' => 'SELECT "table".* FROM "table" ORDER BY isnull("name") DESC, "name" ASC',
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Quantitifier()
    {
        return [
            [
                'sqlObject' => $this->select()->from('foo')->quantifier(Select::QUANTIFIER_DISTINCT),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT DISTINCT "foo".* FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->from('foo')->quantifier(new Expression('TOP ?', [10])),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT TOP \'10\' "foo".* FROM "foo"',
                        'prepare' => 'SELECT TOP ? "foo".* FROM "foo"',
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_SubSelects()
    {
        return [
            [
                'sqlObject' => $this->select(['b' => $this->select(['a' => $this->select('test')])]),
                'expected'  => [
                    'Oracle' => [
                        'string'     => 'SELECT "b".* FROM (SELECT "a".* FROM (SELECT "test".* FROM "test") "a") "b"',
                        'prepare'    => true,
                        'parameters' => [],
                    ],
                ],
            ],
            [ // subselect in join
                'sqlObject' => function () {
                    $subselect = $this->select();
                    $subselect->from('bar')->where->like('y', '%Foo%');
                    return $this->select()
                        ->from('foo')
                        ->join(
                            ['z' => $subselect],
                            'z.foo = bar.id'
                        );
                },
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "z".* FROM "foo" INNER JOIN (SELECT "bar".* FROM "bar" WHERE "y" LIKE \'%Foo%\') AS "z" ON "z"."foo" = "bar"."id"',
                        'prepare' => 'SELECT "foo".*, "z".* FROM "foo" INNER JOIN (SELECT "bar".* FROM "bar" WHERE "y" LIKE ?) AS "z" ON "z"."foo" = "bar"."id"',
                    ],
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
        ];
    }

    public function dataProvider_Table()
    {
        return [
            'without table' => [
                'sqlObject' => $this->select()
                                        ->columns([
                                            new Expression('SOME_DB_FUNCTION_ONE()'),
                                            'foo' => new Expression('SOME_DB_FUNCTION_TWO()'),
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT SOME_DB_FUNCTION_ONE() AS column1, SOME_DB_FUNCTION_TWO() AS "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            'string table' => [
                'sqlObject' => $this->select()->from('foo'),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo"',
                        'prepare' => true,
                    ],
                    'SqlServer' => [
                        'string'     => 'SELECT [foo].* FROM [foo]',
                        'prepare'    => true,
                        'parameters' => [],
                    ],
                ],
            ],
            'string table with alias' => [
                'sqlObject' => $this->select()->from(['x' => 'foo']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "x".* FROM "foo" AS "x"',
                        'prepare' => true,
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT "x".* FROM "foo" "x"',
                        'prepare'    => true,
                        'parameters' => [],
                    ],
                ],
            ],
            'string table with alias and schema' => [
                'sqlObject' => $this->select()->from(['x' => ['bar', 'foo']]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "x".* FROM "bar"."foo" AS "x"',
                        'prepare' => true,
                    ],
                    'Oracle' => [
                        'string'     => 'SELECT "x".* FROM "bar"."foo" "x"',
                        'prepare'    => true,
                        'parameters' => [],
                    ],
                ],
            ],
            'table as TableIdentifier' => [ // table as TableIdentifier
                'sqlObject' => $this->select()->from(new TableIdentifier('foo', 'bar')),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "bar"."foo".* FROM "bar"."foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            'table with alias with table as TableIdentifier' => [
                'sqlObject' => $this->select()->from(['f' => new TableIdentifier('foo')]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "f".* FROM "foo" AS "f"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // Test TableIdentifier In Joins @link https://github.com/zendframework/zf2/issues/3294
                'sqlObject' => $this->select()->from('foo')->columns([])->join(new TableIdentifier('bar', 'baz'), 'm = n', ['thecount' => new Expression("COUNT(*)")]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT COUNT(*) AS "thecount" FROM "foo" INNER JOIN "baz"."bar" ON "m" = "n"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // Test TableIdentifier In Joins, with multiple joins @link https://github.com/zendframework/zf2/issues/3294
                'sqlObject' => $this->select()->from('foo')
                                    ->join(['a' => new TableIdentifier('another_foo', 'another_schema')], 'a.x = foo.foo_column')
                                    ->join('bar', 'foo.colx = bar.colx'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "a".*, "bar".* FROM "foo"'
                                    . ' INNER JOIN "another_schema"."another_foo" AS "a" ON "a"."x" = "foo"."foo_column"'
                                    . ' INNER JOIN "bar" ON "foo"."colx" = "bar"."colx"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ //testSelectUsingTableIdentifierWithEmptyScheme()
                'sqlObject' => $this->select()
                                        ->from(new TableIdentifier('foo'))
                                        ->join(new TableIdentifier('bar'), 'foo.id = bar.fooid'),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".*, "bar".* FROM "foo" INNER JOIN "bar" ON "foo"."id" = "bar"."fooid"',
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Where()
    {
        return [
            [ // Test generic predicate is appended with AND
                'sqlObject' => function () {
                    $select = $this->select();
                    $select->from(new TableIdentifier('foo'))
                            ->where
                            ->nest
                                ->isNull('bar')
                                ->and
                                ->predicate(new Predicate\Literal('1=1'))
                            ->unnest;
                    return $select;
                },
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" WHERE ("bar" IS NULL AND 1=1)',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // Test generic predicate is appended with OR
                'sqlObject' => function () {
                    $select = $this->select();
                    $select->from(new TableIdentifier('foo'))
                            ->where
                            ->nest
                                ->isNull('bar')
                                ->or
                                ->predicate(new Predicate\Literal('1=1'))
                            ->unnest;
                    return $select;
                },
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" WHERE ("bar" IS NULL OR 1=1)',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // where (simple string)
                'sqlObject' => $this->select()->from('foo')->where('x = 5'),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" WHERE x = 5',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // where (returning parameters)
                'sqlObject' => $this->select()->from('foo')->where(['x = ?' => 5]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" WHERE x = \'5\'',
                        'prepare' => 'SELECT "foo".* FROM "foo" WHERE x = ?',
                        'parameters' => ['expr1' => 5],
                    ],
                ],
            ],
            [
                'sqlObject' => function () {
                    $subselect = $this->select();
                    $subselect->from('bar')->where->like('y', '%Foo%');
                    return $this->select()->from(['x' => $subselect]);
                },
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "x".* FROM (SELECT "bar".* FROM "bar" WHERE "y" LIKE \'%Foo%\') AS "x"',
                        'prepare' => 'SELECT "x".* FROM (SELECT "bar".* FROM "bar" WHERE "y" LIKE ?) AS "x"',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select('table')
                                    ->where([
                                        'c1' => null,
                                        'c2' => [1, 2, 3],
                                        new \Zend\Db\Sql\Predicate\IsNotNull('c3')
                                    ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "table".* FROM "table" WHERE "c1" IS NULL AND "c2" IN (\'1\', \'2\', \'3\') AND "c3" IS NOT NULL',
                        'prepare' => 'SELECT "table".* FROM "table" WHERE "c1" IS NULL AND "c2" IN (?, ?, ?) AND "c3" IS NOT NULL',
                    ],
                ],
            ],
        ];
    }
}
