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
 * @covers Zend\Db\Sql\Builder\sql92\Predicate\PredicateSetBuilder
 */
class PredicateSetBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_PredicateSet()
                                        ->addPredicate($this->predicate_IsNull('foo'))
                                        ->addPredicate($this->predicate_IsNull('bar')),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo" IS NULL AND "bar" IS NULL',
                        'prepare' => '"foo" IS NULL AND "bar" IS NULL',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_PredicateSet(
                                        [
                                            $this->predicate_IsNull('foo'),
                                            $this->predicate_IsNull('bar'),
                                        ],
                                        'OR'
                                    ),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo" IS NULL OR "bar" IS NULL',
                        'prepare' => '"foo" IS NULL OR "bar" IS NULL',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_PredicateSet()
                                        ->addPredicate($this->predicate_IsNull('foo'), 'OR')
                                        ->addPredicate($this->predicate_IsNull('bar'), 'AND')
                                        ->addPredicate($this->predicate_IsNull('baz'), 'OR')
                                        ->addPredicate($this->predicate_IsNull('bat'), 'AND'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo" IS NULL AND "bar" IS NULL OR "baz" IS NULL AND "bat" IS NULL',
                        'prepare' => '"foo" IS NULL AND "bar" IS NULL OR "baz" IS NULL AND "bat" IS NULL',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_PredicateSet()
                                        ->orPredicate($this->predicate_IsNull('foo'))
                                        ->andPredicate($this->predicate_IsNull('bar'))
                                        ->orPredicate($this->predicate_IsNull('baz'))
                                        ->andPredicate($this->predicate_IsNull('bat')),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo" IS NULL AND "bar" IS NULL OR "baz" IS NULL AND "bat" IS NULL',
                        'prepare' => '"foo" IS NULL AND "bar" IS NULL OR "baz" IS NULL AND "bat" IS NULL',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => function () {
                    $select = $this->select()->from('x');
                    $select->where->like('bar', 'Foo%');
                    return $this->predicate_PredicateSet([
                        $this->predicate_PredicateSet([
                            $this->predicate_In('x', $select)
                        ])
                    ]);
                },
                'expected'  => [
                    'sql92' => [
                        'string'  => '("x" IN (SELECT "x".* FROM "x" WHERE "bar" LIKE \'Foo%\'))',
                        'prepare' => '("x" IN (SELECT "x".* FROM "x" WHERE "bar" LIKE ?))',
                        'parameters' => [
                            'subselect1expr1' => 'Foo%',
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_PredicateSet([
                    $this->predicate_PredicateSet([
                        $this->predicate_Expression('x = ?', 5)
                    ])
                ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => "(x = '5')",
                        'prepare' => '(x = ?)',
                        'parameters' => [
                            'expr1' => 5,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
