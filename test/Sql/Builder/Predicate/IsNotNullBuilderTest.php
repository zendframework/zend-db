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
 * @covers Zend\Db\Sql\Builder\sql92\Predicate\IsNotNullBuilder
 */
class IsNotNullBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_IsNotNull('foo.bar'),
                'expected'  => [
                    'sql92' => [
                        'string'  => '"foo"."bar" IS NOT NULL',
                        'prepare' => '"foo"."bar" IS NOT NULL',
                        'parameters' => [],
                    ],
                ],
            ],
        ]);
    }
}