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
 * @covers Zend\Db\Sql\Builder\sql92\Predicate\OperatorBuilder
 */
class OperatorBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_Operator()
                                        ->setLeft(['foo', ExpressionInterface::TYPE_VALUE])
                                        ->setOperator('>=')
                                        ->setRight(['foo.bar', ExpressionInterface::TYPE_IDENTIFIER]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '\'foo\' >= "foo"."bar"',
                        'prepare' => '? >= "foo"."bar"',
                        'parameters' => [
                            'expr1' => 'foo',
                        ],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Operator(
                    'release_date',
                    '=',
                    $this->predicate_Expression('FROM_UNIXTIME(?)', 100000000)
                ),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"release_date" = FROM_UNIXTIME(\'100000000\')',
                        'prepare' => '"release_date" = FROM_UNIXTIME(?)',
                        'parameters' => [
                            'expr1' => 100000000,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
