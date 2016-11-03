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
 * @covers Zend\Db\Sql\Builder\sql92\Predicate\LikeBuilder
 */
class LikeBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_Like('bar', 'Foo%'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"bar" LIKE \'Foo%\'',
                        'prepare' => '"bar" LIKE ?',
                        'parameters' => ['expr1' => 'Foo%'],
                    ],
                ],
            ],
            [
                'sqlObject' => $this->predicate_Like(['Foo%'=>ExpressionInterface::TYPE_VALUE], ['bar'=>ExpressionInterface::TYPE_IDENTIFIER]),
                'expected'  => [
                    'sql92' => [
                        'string'  => '\'Foo%\' LIKE "bar"',
                        'prepare' => '? LIKE "bar"',
                        'parameters' => ['expr1' => 'Foo%'],
                    ],
                ],
            ],
        ]);
    }
}
