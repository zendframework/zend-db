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
        return $this->prepareDataProvider([
            [ // testPrepareStatement()
                'sqlObject' => $this->insert()
                                        ->into('foo')
                                        ->values([
                                            'bar' => 'baz',
                                            'boo' => new Expression('NOW()'),
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '',
                        'prepare' => 'INSERT INTO "foo" ("bar", "boo") VALUES (?, NOW())',
                    ],
                ],
            ],
            [ // testPrepareStatement() // with TableIdentifier
                'sqlObject' => $this->insert()
                                        ->into(new TableIdentifier('foo', 'sch'))
                                        ->values([
                                            'bar' => 'baz',
                                            'boo' => new Expression('NOW()'),
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '',
                        'prepare' => 'INSERT INTO "sch"."foo" ("bar", "boo") VALUES (?, NOW())'
                    ],
                ],
            ],
            [ // testPrepareStatementWithSelect()
                'sqlObject' => $this->insert()
                                        ->into('foo')
                                        ->columns(['col1'])
                                        ->select($this->select('bar')->where(['x'=>5])),
                'expected'  => [
                    'sql92' => [
                        'string'  => '',
                        'prepare' => 'INSERT INTO "foo" ("col1") SELECT "bar".* FROM "bar" WHERE "x" = ?',
                        'parameters' => ['subselect1expr1'=>5],
                    ],
                ],
            ],
            [ // testGetSqlString()
                'sqlObject' => $this->insert()->into('foo')
                                        ->values([
                                            'bar' => 'baz',
                                            'boo' => new Expression('NOW()'),
                                            'bam' => null
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'INSERT INTO "foo" ("bar", "boo", "bam") VALUES (\'baz\', NOW(), NULL)',
                    ],
                ],
            ],
            [ // testGetSqlString() // with TableIdentifier
                'sqlObject' => $this->insert()
                                        ->into(new TableIdentifier('foo', 'sch'))
                                        ->values([
                                            'bar' => 'baz',
                                            'boo' => new Expression('NOW()'),
                                            'bam' => null
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'INSERT INTO "sch"."foo" ("bar", "boo", "bam") VALUES (\'baz\', NOW(), NULL)',
                    ],
                ],
            ],
            [ // testGetSqlString() // with Select
                'sqlObject' => $this->insert()
                                        ->into('foo')
                                        ->select($this->select()->from('bar')),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'INSERT INTO "foo"  SELECT "bar".* FROM "bar"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // testGetSqlString() // with Select and columns
                'sqlObject' => $this->insert()
                                        ->into('foo')
                                        ->columns(['col1', 'col2'])
                                        ->select($this->select()->from('bar')),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'INSERT INTO "foo" ("col1", "col2") SELECT "bar".* FROM "bar"',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // testValuesMerge()
                'sqlObject' => $this->insert()
                                        ->into('foo')
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
        ]);
    }
}
