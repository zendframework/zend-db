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
 * @covers Zend\Db\Sql\Builder\sql92\Predicate\NotInBuilder
 */
class NotInBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_NotIn('bar')
                                        ->setIdentifier('foo.bar')
                                        ->setValueSet([1, 2, 3]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" NOT IN (\'1\', \'2\', \'3\')',
                        'prepare' => '"foo"."bar" NOT IN (?, ?, ?)',
                        'parameters' => [
                            'expr1' => 1,
                            'expr2' => 2,
                            'expr3' => 3,
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_NotIn('foo', $this->select('bar')),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo" NOT IN (SELECT "bar".* FROM "bar")',
                        'prepare' => '"foo" NOT IN (SELECT "bar".* FROM "bar")',
                        'parameters' => [],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_NotIn(['foo', 'bar'], $this->select('bar')),
                'expected'  => [
                    'sql92' => [
                        'string'  => '("foo", "bar") NOT IN (SELECT "bar".* FROM "bar")',
                        'prepare' => '("foo", "bar") NOT IN (SELECT "bar".* FROM "bar")',
                        'parameters' => [],
                    ],
                ],
            ],
        ]);
    }
}
