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
 * @covers Zend\Db\Sql\Builder\sql92\Predicate\BetweenBuilder
 */
class BetweenBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_Between('foo.bar', 5, 10),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" BETWEEN \'5\' AND \'10\'',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Between()
                                        ->setIdentifier([10=>ExpressionInterface::TYPE_VALUE])
                                        ->setMinValue(['foo.bar'=>ExpressionInterface::TYPE_IDENTIFIER])
                                        ->setMaxValue(['foo.baz'=>ExpressionInterface::TYPE_IDENTIFIER]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '\'10\' BETWEEN "foo"."bar" AND "foo"."baz"',
                    ],
                ],
            ],
        ]);
    }
}
