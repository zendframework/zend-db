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
            $this->dataProvider_SQL92(),
            $this->dataProvider_SqlServer(),
            $this->dataProvider_Oracle(),
            $this->dataProvider_Mysql(),
            $this->dataProvider_IbmDb2(),
            $this->dataProvider_ForDifferentAdapters()
        );
    }

    /**
     * ZendTest\Db\Sql::testForDifferentAdapters()
     */
    public function dataProvider_ForDifferentAdapters()
    {
        $select = $this->select('foo')->offset(10);
        return [
            [
                'sqlObject' => $select,
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo" OFFSET \'10\'',
                        'prepare'    => 'SELECT "foo".* FROM "foo" OFFSET ?',
                    ],
                ],
            ],
            [
                'sqlObject' => $select,
                'expected'  => [
                    'MySql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 18446744073709551615 OFFSET 10',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT 18446744073709551615 OFFSET ?',
                    ],
                ],
            ],
            [
                'sqlObject' => $select,
                'expected'  => [
                    'Oracle' => [
                        'string'     => 'SELECT * FROM (SELECT b.*, rownum b_rownum FROM ( SELECT "foo".* FROM "foo" ) b ) WHERE b_rownum > (10)',
                        'prepare'    => 'SELECT * FROM (SELECT b.*, rownum b_rownum FROM ( SELECT "foo".* FROM "foo" ) b ) WHERE b_rownum > (:offset)',
                    ],
                ],
            ],
            [
                'sqlObject' => $select,
                'expected'  => [
                    'SqlServer' => [
                        'string'  => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN 10+1 AND 0+10',
                        'prepare' => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN ?+1 AND ?+?',
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_IbmDb2()
    {
        return [
            [
                'sqlObject' => $this->select()->from(['x' => 'foo'])->limit(5),
                'expected'  => [
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM ( SELECT "x".*, ROW_NUMBER() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN 0 AND 5',
                        'prepare'    => 'SELECT * FROM ( SELECT "x".*, ROW_NUMBER() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN ? AND ?',
                        'parameters' => ['offset' => 0, 'limit' => 5],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->from(['x' => 'foo'])->limit(5)->offset(10),
                'expected'  => [
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM ( SELECT "x".*, ROW_NUMBER() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN 11 AND 15',
                        'prepare'    => 'SELECT * FROM ( SELECT "x".*, ROW_NUMBER() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN ? AND ?',
                        'parameters' => ['offset' => 11, 'limit' => 15],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->columns([new Expression('DISTINCT(id) as id')])->from(['x' => 'foo'])->limit(5)->offset(10),
                'expected'  => [
                    'IbmDb2' => [
                        'string'     => 'SELECT DISTINCT(id) as id FROM ( SELECT DISTINCT(id) as id, DENSE_RANK() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN 11 AND 15',
                        'prepare'    => 'SELECT DISTINCT(id) as id FROM ( SELECT DISTINCT(id) as id, DENSE_RANK() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN ? AND ?',
                        'parameters' => ['offset' => 11, 'limit' => 15],
                    ],
                ],
            ],
            [
                'sqlObject' => function () {
                    $select = $this->select()->from(['x' => 'foo'])->limit(5)->offset(10);
                    $select->where->greaterThan('x.id', '10')->AND->lessThan('x.id', '31');
                    return $select;
                },
                'expected'  => [
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM ( SELECT "x".*, ROW_NUMBER() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" WHERE "x"."id" > \'10\' AND "x"."id" < \'31\' ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN 11 AND 15',
                        'prepare'    => 'SELECT * FROM ( SELECT "x".*, ROW_NUMBER() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" WHERE "x"."id" > ? AND "x"."id" < ? ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN ? AND ?',
                        'parameters' => ['expr1' => '10', 'expr2' => '31', 'offset' => 11, 'limit' => 15],
                    ],
                ],
            ],
            [
                'sqlObject' => function () {
                    $select = $this->select()->from(['x' => 'foo'])->limit(5);
                    $select->where->greaterThan('x.id', '10')->AND->lessThan('x.id', '31');
                    return $select;
                },
                'expected'  => [
                    'IbmDb2' => [
                        'string'     => 'SELECT * FROM ( SELECT "x".*, ROW_NUMBER() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" WHERE "x"."id" > \'10\' AND "x"."id" < \'31\' ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN 0 AND 5',
                        'prepare'    => 'SELECT * FROM ( SELECT "x".*, ROW_NUMBER() OVER () AS ZEND_DB_ROWNUM FROM "foo" "x" WHERE "x"."id" > ? AND "x"."id" < ? ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN ? AND ?',
                        'parameters' => ['expr1' => '10', 'expr2' => '31', 'offset' => 0, 'limit' => 5],
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Mysql()
    {
        return [
            [
                'sqlObject' => $this->select()->from('foo')->limit(5)->offset(10),
                'expected'  => [
                    'Mysql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 5 OFFSET 10',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => 5, 'offset' => 10],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->from('foo')->offset(10),
                'expected'  => [
                    'Mysql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 18446744073709551615 OFFSET 10',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT 18446744073709551615 OFFSET ?',
                        'parameters' => ['offset' => 10],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->from('foo')->limit('5')->offset('10000000000000000000'),
                'expected'  => [
                    'Mysql' => [
                        'string'     => 'SELECT `foo`.* FROM `foo` LIMIT 5 OFFSET 10000000000000000000',
                        'prepare'    => 'SELECT `foo`.* FROM `foo` LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => '5', 'offset' => '10000000000000000000'],
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Oracle()
    {
        return [
            [
                'sqlObject' => $this->select()->from(['x' => 'foo']),
                'expected'  => [
                    'Oracle' => [
                        'string'     => 'SELECT "x".* FROM "foo" "x"',
                        'prepare'    => true,
                        'parameters' => [],
                    ],
                ],
            ],
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
        ];
    }

    public function dataProvider_SqlServer()
    {
        return [
            [
                'sqlObject' => $this->select()->from('foo')->columns(['bar', 'baz'])->order('bar')->limit(5)->offset(10),
                'expected'  => [
                    'SqlServer' => [
                        'string'     => 'SELECT [bar], [baz] FROM ( SELECT [foo].[bar] AS [bar], [foo].[baz] AS [baz], ROW_NUMBER() OVER (ORDER BY [bar] ASC) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN 10+1 AND 5+10',
                        'prepare'    => 'SELECT [bar], [baz] FROM ( SELECT [foo].[bar] AS [bar], [foo].[baz] AS [baz], ROW_NUMBER() OVER (ORDER BY [bar] ASC) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN ?+1 AND ?+?',
                        'parameters' => ['offset' => 10, 'limit' => 5, 'offsetForSum' => 10],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->from('foo')->columns(['bar', 'bam' => 'baz'])->limit(5)->offset(10),
                'expected'  => [
                    'SqlServer' => [
                        'string'     => 'SELECT [bar], [bam] FROM ( SELECT [foo].[bar] AS [bar], [foo].[baz] AS [bam], ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN 10+1 AND 5+10',
                        'prepare'    => 'SELECT [bar], [bam] FROM ( SELECT [foo].[bar] AS [bar], [foo].[baz] AS [bam], ROW_NUMBER() OVER (ORDER BY (SELECT 1)) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN ?+1 AND ?+?',
                        'parameters' => ['offset' => 10, 'limit' => 5, 'offsetForSum' => 10],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->from('foo')->order('bar')->limit(5)->offset(10),
                'expected'  => [
                    'SqlServer' => [
                        'string'     => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY [bar] ASC) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN 10+1 AND 5+10',
                        'prepare'    => 'SELECT * FROM ( SELECT [foo].*, ROW_NUMBER() OVER (ORDER BY [bar] ASC) AS [__ZEND_ROW_NUMBER] FROM [foo] ) AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN ?+1 AND ?+?',
                        'parameters' => ['offset' => 10, 'limit' => 5, 'offsetForSum' => 10],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->select()->from('foo'),
                'expected'  => [
                    'SqlServer' => [
                        'string'     => 'SELECT [foo].* FROM [foo]',
                        'prepare'    => true,
                        'parameters' => [],
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_SQL92()
    {
        return [
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
            [ //basic table
                'sqlObject' => $this->select()->from('foo'),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "foo".* FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // table as TableIdentifier
                'sqlObject' => $this->select()->from(new TableIdentifier('foo', 'bar')),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "bar"."foo".* FROM "bar"."foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // table with alias
                'sqlObject' => $this->select()->from(['f' => 'foo']),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "f".* FROM "foo" AS "f"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // table with alias with table as TableIdentifier
                'sqlObject' => $this->select()->from(['f' => new TableIdentifier('foo')]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'SELECT "f".* FROM "foo" AS "f"',
                        'prepare' => true,
                    ],
                ],
            ],
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
            [ // limit
                'sqlObject' => $this->select()->from('foo')->limit(5),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" LIMIT \'5\'',
                        'prepare' => 'SELECT "foo".* FROM "foo" LIMIT ?',
                        'parameters' => ['limit' => 5],
                    ],
                ],
            ],
            [ // limit with offset
                'sqlObject' => $this->select()->from('foo')->limit(5)->offset(10),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" LIMIT \'5\' OFFSET \'10\'',
                        'prepare' => 'SELECT "foo".* FROM "foo" LIMIT ? OFFSET ?',
                        'parameters' => ['limit' => 5, 'offset' => 10],
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
            [ // order with compound name
                'sqlObject' => $this->select()->from('foo')->order('c1.d2'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" ORDER BY "c1"."d2" ASC',
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
            [ // join with expression in ON part
                'sqlObject' => $this->select()->from('foo')->join('zac', new Expression('(m = n AND c.x) BETWEEN x AND y.z')),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "zac".* FROM "foo" INNER JOIN "zac" ON (m = n AND c.x) BETWEEN x AND y.z',
                        'prepare' => true,
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
                'sqlObject' => $this->select()
                                    ->from('table')
                                    ->columns(['*'])
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
                                        . ' INNER JOIN "tableA" ON "id" = :join1expr1 INNER JOIN "tableB" ON "id" = :join2expr1 '
                                        . 'INNER JOIN "tableC" ON "id" = :join3expr1 AND "number" > :join3expr2',
                        'useNamedParams' => true,
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
            [ // Test TableIdentifier In Joins @link https://github.com/zendframework/zf2/issues/3294
                'sqlObject' => $this->select()->from('foo')->columns([])->join(new TableIdentifier('bar', 'baz'), 'm = n', ['thecount' => new Expression("COUNT(*)")]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT COUNT(*) AS "thecount" FROM "foo" INNER JOIN "baz"."bar" ON "m" = "n"',
                        'prepare' => true,
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
            [
                'sqlObject' => $this->select()->from(['x' => 'foo'])->columns(['bar' => 'foo.bar'], false),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo"."bar" AS "bar" FROM "foo" AS "x"',
                        'prepare' => 'SELECT "foo"."bar" AS "bar" FROM "foo" AS "x"',
                    ],
                ],
            ],
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
                        'string'  => '( SELECT "foo".* FROM "foo" WHERE a = b ) UNION ALL ( SELECT "bar".* FROM "bar" WHERE c = d )',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // limit with offset
                'sqlObject' => $this->select()->from('foo')->limit("5")->offset("10"),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" LIMIT \'5\' OFFSET \'10\'',
                        'prepare' => 'SELECT "foo".* FROM "foo" LIMIT ? OFFSET ?',
                        'parametersEquals' => ['limit' => 5, 'offset' => 10],
                    ],
                ],
            ],
            [ // functions without table
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
            [ // limit with big offset and limit
                'sqlObject' => $this->select()->from('foo')->limit("10000000000000000000")->offset("10000000000000000000"),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".* FROM "foo" LIMIT \'10000000000000000000\' OFFSET \'10000000000000000000\'',
                        'prepare' => 'SELECT "foo".* FROM "foo" LIMIT ? OFFSET ?',
                        'parametersEquals' => ['limit' => 10000000000000000000, 'offset' => 10000000000000000000],
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
                        'string'  => 'SELECT "sub".* FROM (( SELECT "foo".* FROM "foo" WHERE a = b ) UNION ( SELECT "bar".* FROM "bar" WHERE c = d )) AS "sub" ORDER BY "id" DESC',
                        'prepare' => true,
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
            [ // @author Andrzej Lewandowski @link https://github.com/zendframework/zf2/issues/7222
                'sqlObject' => $this->select()->from('foo')->join('zac', '(catalog_category_website.category_id = catalog_category.category_id)'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'SELECT "foo".*, "zac".* FROM "foo" INNER JOIN "zac" ON ("catalog_category_website"."category_id" = "catalog_category"."category_id")',
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
}
