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

/**
 * @covers Zend\Db\Sql\Builder\sql92\DeleteBuilder
 */
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
        return $this->prepareDataProvider(
            $this->dataProvider_From(),
            $this->dataProvider_Subselect(),
            $this->dataProvider_Where()
        );
    }

    public function dataProvider_From()
    {
        return $this->prepareDataProvider([
            [
                'sqlObject' => $this->delete()->from('foo'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'DELETE FROM "foo"',
                        'prepare' => true,
                    ],
                ],
            ],
            [
                'sqlObject' => $this->delete()->from(new TableIdentifier('foo', 'sch')),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'DELETE FROM "sch"."foo"',
                        'prepare' => true,
                    ],
                ],
            ],
        ]);
    }

    public function dataProvider_Subselect()
    {
        return [
            [
                'sqlObject' => $this->delete('foo')->where(['x'=>$this->select('foo')->where(['x'=>'y'])]),
                'expected'  => [
                    'sql92'     => [
                        'string'     => 'DELETE FROM "foo" WHERE "x" = (SELECT "foo".* FROM "foo" WHERE "x" = \'y\')',
                        'prepare'    => 'DELETE FROM "foo" WHERE "x" = (SELECT "foo".* FROM "foo" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'MySql'     => [
                        'string'     => 'DELETE FROM `foo` WHERE `x` = (SELECT `foo`.* FROM `foo` WHERE `x` = \'y\')',
                        'prepare'    => 'DELETE FROM `foo` WHERE `x` = (SELECT `foo`.* FROM `foo` WHERE `x` = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'Oracle'    => [
                        'string'     => 'DELETE FROM "foo" WHERE "x" = (SELECT "foo".* FROM "foo" WHERE "x" = \'y\')',
                        'prepare'    => 'DELETE FROM "foo" WHERE "x" = (SELECT "foo".* FROM "foo" WHERE "x" = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                    'SqlServer' => [
                        'string'     => 'DELETE FROM [foo] WHERE [x] = (SELECT [foo].* FROM [foo] WHERE [x] = \'y\')',
                        'prepare'    => 'DELETE FROM [foo] WHERE [x] = (SELECT [foo].* FROM [foo] WHERE [x] = ?)',
                        'parameters' => ['subselect1expr1' => 'y'],
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Where()
    {
        return [
            [ // testPrepareSqlStatement(), testBuildSqlString()
                'sqlObject' => $this->delete()->from('foo')->where('x = y'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'DELETE FROM "foo" WHERE x = y',
                        'prepare' => true,
                    ],
                ],
            ],
        ];
    }
}
