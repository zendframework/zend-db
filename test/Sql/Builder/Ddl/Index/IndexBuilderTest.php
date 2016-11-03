<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Ddl\Index;

use ZendTest\Db\Sql\Builder\AbstractTestCase;

/**
 * @covers Zend\Db\Sql\Builder\sql92\Ddl\Index\IndexBuilder
 */
class IndexBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->index_Index('foo', 'my_uk'),
                'expected'  => [
                    'sql92' => 'INDEX "my_uk"("foo")',
                ],
            ],
            [
                'sqlObject' => $this->index_Index(['foo', 'bar'], 'my_uk', [10, 5]),
                'expected'  => [
                    'sql92' => 'INDEX "my_uk"("foo"(10), "bar"(5))',
                ],
            ],
            [
                'sqlObject' => $this->index_Index(['foo', 'bar'], 'my_uk', [10]),
                'expected'  => [
                    'sql92' => 'INDEX "my_uk"("foo"(10), "bar")',
                ],
            ],
        ]);
    }
}
