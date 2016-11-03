<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\ExpressionInterface;

/**
 * @covers Zend\Db\Sql\Builder\sql92\ExpressionBuilder
 */
class ExpressionBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_Expression()->setExpression('foo.bar = ? AND id != ?')->setParameters(['foo', 'bar']),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'foo.bar = \'foo\' AND id != \'bar\'',
                        'prepare' => 'foo.bar = ? AND id != ?',
                        'parameters' => [
                            'expr1' => 'foo',
                            'expr2' => 'bar',
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->expression(
                                        'X SAME AS ? AND Y = ? BUT LITERALLY ?',
                                        [
                                            ['foo',        ExpressionInterface::TYPE_IDENTIFIER],
                                            [5,            ExpressionInterface::TYPE_VALUE],
                                            ['FUNC(FF%X)', ExpressionInterface::TYPE_LITERAL],
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'X SAME AS "foo" AND Y = \'5\' BUT LITERALLY FUNC(FF%X)',
                        'prepare' => 'X SAME AS "foo" AND Y = ? BUT LITERALLY FUNC(FF%X)',
                        'parameters' => [
                            'expr1' => 5,
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->expression('X LIKE "foo%"'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'X LIKE "foo%"',
                        'prepare' => 'X LIKE "foo%"',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->expression('? LIKE "foo%"', [['X', 'literal']]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'X LIKE "foo%"',
                        'prepare' => 'X LIKE "foo%"',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->expression(
                                        '? > ? AND y < ?',
                                        [
                                            ['x', ExpressionInterface::TYPE_IDENTIFIER],
                                            5,
                                            10
                                        ]
                                    ),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"x" > \'5\' AND y < \'10\'',
                        'prepare' => '"x" > ? AND y < ?',
                        'parameters' => [
                            'expr1' => 5,
                            'expr2' => 10,
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->expression(
                                        '? > ? AND y < ?',
                                        [
                                            ['x', ExpressionInterface::TYPE_IDENTIFIER],
                                            5,
                                            10
                                        ]
                                    ),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"x" > \'5\' AND y < \'10\'',
                        'prepare' => '"x" > :expr1 AND y < :expr2',
                        'parameters' => [
                            'expr1' => 5,
                            'expr2' => 10,
                        ],
                        'useNamedParams' => true,
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_PredicateSet([$this->predicate_PredicateSet([$this->predicate_Expression('x = ?', 5)])]),
                'expected'  => [
                    'sql92' => [
                        'string'  => "(x = '5')",
                        'prepare' => "(x = ?)",
                        'parameters' => [
                            'expr1' => 5,
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_PredicateSet([
                    $this->predicate_PredicateSet([
                        $this->predicate_In(
                            'x',
                            $this->select('x')->where($this->predicate_Like('bar', 'Foo%'))
                        )
                    ])
                ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '("x" IN (SELECT "x".* FROM "x" WHERE "bar" LIKE \'Foo%\'))',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Operator(
                    'release_date',
                    '=',
                    $this->expression('FROM_UNIXTIME(?)', 100000000)
                ),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"release_date" = FROM_UNIXTIME(\'100000000\')',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->expression('FROM_UNIXTIME(date, "%Y-%m")'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'FROM_UNIXTIME(date, "%Y-%m")',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->expression('uf.user_id = :user_id OR uf.friend_id = :user_id', ['user_id' => 1]),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'uf.user_id = :user_id OR uf.friend_id = :user_id',
                        'prepare' => 'uf.user_id = :user_id OR uf.friend_id = :user_id',
                    ],
                ],
            ],
        ]);
    }
}
