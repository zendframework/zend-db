<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use ZendTest\Db\Sql\Builder\AbstractTestCase;

/**
 * @covers Zend\Db\Sql\Builder\sql92\Predicate\PredicateBuilder
 */
class PredicateBuilderTest extends AbstractTestCase
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
            [
                'sqlObject' => $this->predicate_Predicate()->equalTo('foo.bar', 'bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" = \'bar\'',
                        'prepare' => '"foo"."bar" = ?',
                        'parameters' => ['expr1' => 'bar'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->notEqualTo('foo.bar', 'bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" != \'bar\'',
                        'prepare' => '"foo"."bar" != ?',
                        'parameters' => ['expr1' => 'bar'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->lessThan('foo.bar', 'bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" < \'bar\'',
                        'prepare' => '"foo"."bar" < ?',
                        'parameters' => ['expr1' => 'bar'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->greaterThan('foo.bar', 'bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" > \'bar\'',
                        'prepare' => '"foo"."bar" > ?',
                        'parameters' => ['expr1' => 'bar'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->lessThanOrEqualTo('foo.bar', 'bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" <= \'bar\'',
                        'prepare' => '"foo"."bar" <= ?',
                        'parameters' => ['expr1' => 'bar'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->greaterThanOrEqualTo('foo.bar', 'bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" >= \'bar\'',
                        'prepare' => '"foo"."bar" >= ?',
                        'parameters' => ['expr1' => 'bar'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->like('foo.bar', 'bar%'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" LIKE \'bar%\'',
                        'prepare' => '"foo"."bar" LIKE ?',
                        'parameters' => ['expr1' => 'bar%'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->notLike('foo.bar', 'bar%'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" NOT LIKE \'bar%\'',
                        'prepare' => '"foo"."bar" NOT LIKE ?',
                        'parameters' => ['expr1' => 'bar%'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->literal('foo.bar = ?', 'bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'foo.bar = \'bar\'',
                        'prepare' => 'foo.bar = ?',
                        'parameters' => ['expr1' => 'bar'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->isNull('foo.bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" IS NULL',
                        'prepare' => '"foo"."bar" IS NULL',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->isNotNull('foo.bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" IS NOT NULL',
                        'prepare' => '"foo"."bar" IS NOT NULL',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->in('foo.bar', ['foo', 'bar']),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" IN (\'foo\', \'bar\')',
                        'prepare' => '"foo"."bar" IN (?, ?)',
                        'parameters' => [
                            'expr1' => 'foo',
                            'expr2' => 'bar',
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->notIn('foo.bar', ['foo', 'bar']),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" NOT IN (\'foo\', \'bar\')',
                        'prepare' => '"foo"."bar" NOT IN (?, ?)',
                        'parameters' => [
                            'expr1' => 'foo',
                            'expr2' => 'bar',
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->between('foo.bar', 1, 10),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" BETWEEN \'1\' AND \'10\'',
                        'prepare' => '"foo"."bar" BETWEEN ? AND ?',
                        'parameters' => [
                            'expr1' => 1,
                            'expr2' => 10,
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->notBetween('foo.bar', 1, 10),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" NOT BETWEEN \'1\' AND \'10\'',
                        'prepare' => '"foo"."bar" NOT BETWEEN ? AND ?',
                        'parameters' => [
                            'expr1' => 1,
                            'expr2' => 10,
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->expression('foo = ?', 'bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'foo = \'bar\'',
                        'prepare' => 'foo = ?',
                        'parameters' => ['expr1' => 'bar'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->expression('foo = ?', 0),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'foo = \'0\'',
                        'prepare' => 'foo = ?',
                        'parameters' => ['expr1' => 0],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()->expression('foo = ?'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'foo = \'\'',
                        'prepare' => 'foo = ?',
                        'parameters' => ['expr1' => null],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()
                                        ->isNull('foo.bar')
                                        ->or
                                        ->isNotNull('bar.baz')
                                        ->and
                                        ->equalTo('baz.bat', 'foo'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" IS NULL OR "bar"."baz" IS NOT NULL AND "baz"."bat" = \'foo\'',
                        'prepare' => '"foo"."bar" IS NULL OR "bar"."baz" IS NOT NULL AND "baz"."bat" = ?',
                        'parameters' => ['expr1' => 'foo'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Predicate()
                                        ->isNull('foo.bar')
                                        ->nest()
                                        ->isNotNull('bar.baz')
                                        ->and
                                        ->equalTo('baz.bat', 'foo')
                                        ->unnest(),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" IS NULL AND ("bar"."baz" IS NOT NULL AND "baz"."bat" = \'foo\')',
                        'prepare' => '"foo"."bar" IS NULL AND ("bar"."baz" IS NOT NULL AND "baz"."bat" = ?)',
                        'parameters' => ['expr1' => 'foo'],
                    ],
                ],
            ],
        ]);
    }
}
