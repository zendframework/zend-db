<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Ddl\Constraint;

use ZendTest\Db\Sql\Builder\AbstractTestCase;

/**
 * @covers Zend\Db\Sql\Builder\sql92\Ddl\Constraint\UniqueKeyBuilder
 */
class UniqueKeyBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->constraint_UniqueKey('foo', 'my_uk'),
                'expected'  => [
                    'sql92' => 'CONSTRAINT "my_uk" UNIQUE ("foo")',
                ],
            ],
            [
                'sqlObject' => $this->constraint_UniqueKey('foo'),
                'expected'  => [
                    'sql92' => 'UNIQUE ("foo")',
                ],
            ],
        ]);
    }
}
