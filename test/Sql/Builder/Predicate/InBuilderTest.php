<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\ExpressionInterface;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

/**
 * @covers Zend\Db\Sql\Builder\sql92\Predicate\InBuilder
 */
class InBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_In()
                                        ->setIdentifier('foo.bar')
                                        ->setValueSet([1, 2, 3]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" IN (\'1\', \'2\', \'3\')',
                        'prepare' => '"foo"."bar" IN (?, ?, ?)',
                        'parameters' => [
                            'expr1' => 1,
                            'expr2' => 2,
                            'expr3' => 3,
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_In()
                                        ->setIdentifier('foo.bar')
                                        ->setValueSet([
                                            [1=>ExpressionInterface::TYPE_LITERAL],
                                            [2=>ExpressionInterface::TYPE_VALUE],
                                            [3=>ExpressionInterface::TYPE_LITERAL],
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" IN (1, \'2\', 3)',
                        'prepare' => '"foo"."bar" IN (1, ?, 3)',
                        'parameters' => [
                            'expr1' => 2,
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_In('foo', $this->select('bar')),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo" IN (SELECT "bar".* FROM "bar")',
                        'prepare' => '"foo" IN (SELECT "bar".* FROM "bar")',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_In('foo', $this->combine([$this->select('bar'), $this->select('baz')])),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo" IN ((SELECT "bar".* FROM "bar") UNION (SELECT "baz".* FROM "baz"))',
                        'prepare' => '"foo" IN ((SELECT "bar".* FROM "bar") UNION (SELECT "baz".* FROM "baz"))',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_In(['foo', 'bar'], $this->select('bar')),
                'expected'  => [
                    'sql92' => [
                        'string'  => '("foo", "bar") IN (SELECT "bar".* FROM "bar")',
                        'prepare' => '("foo", "bar") IN (SELECT "bar".* FROM "bar")',
                        'parameters' => [],
                    ],
                ],
            ],
        ]);
    }
}
