<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\TableIdentifier;

class DeleteBuilderTest extends AbstractTestCase
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
            [ // testPrepareStatement(), testGetSqlString()
                'sqlObject' => $this->delete()->from('foo')->where('x = y'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'DELETE FROM "foo" WHERE x = y',
                        'prepare' => true,
                    ],
                ],
            ],
            [ // testPrepareStatement(), testGetSqlString() // with TableIdentifier
                'sqlObject' => $this->delete()->from(new TableIdentifier('foo', 'sch'))->where('x = y'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'DELETE FROM "sch"."foo" WHERE x = y',
                        'prepare' => true,
                    ],
                ],
            ],
        ]);
    }
}
