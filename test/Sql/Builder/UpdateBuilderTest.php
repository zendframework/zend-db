<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\TableIdentifier;

class UpdateBuilderTest extends AbstractTestCase
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
            [ //??? testPassingMultipleKeyValueInWhereClause()
                'sqlObject' => $this->update()
                                        ->table('table')
                                        ->set(['fld1' => 'val1'])
                                        ->where(['id1' => 'val1', 'id2' => 'val2']),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'UPDATE "table" SET "fld1" = \'val1\' WHERE "id1" = \'val1\' AND "id2" = \'val2\'',
                    ],
                ],
            ],
            [ // testPrepareStatement(), testGetSqlString()
                'sqlObject' => $this->update()
                                        ->table('foo')
                                        ->set(['bar' => 'baz', 'boo' => new Expression('NOW()'), 'bam' => null])
                                        ->where('x = y'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'UPDATE "foo" SET "bar" = \'baz\', "boo" = NOW(), "bam" = NULL WHERE x = y',
                        'prepare' => 'UPDATE "foo" SET "bar" = ?, "boo" = NOW(), "bam" = NULL WHERE x = y',
                    ],
                ],
            ],
            [ // testPrepareStatement() // with TableIdentifier
                'sqlObject' => $this->update()
                                        ->table(new TableIdentifier('foo', 'sch'))
                                        ->set(['bar' => 'baz', 'boo' => new Expression('NOW()'), 'bam' => null])
                                        ->where('x = y'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'UPDATE "sch"."foo" SET "bar" = \'baz\', "boo" = NOW(), "bam" = NULL WHERE x = y',
                        'prepare' => 'UPDATE "sch"."foo" SET "bar" = ?, "boo" = NOW(), "bam" = NULL WHERE x = y',
                    ],
                ],
            ],
            [ // testGetSqlStringForFalseUpdateValueParameter()
                'sqlObject' => $this->update()
                                        ->table(new TableIdentifier('foo', 'sch'))
                                        ->set(['bar' => false, 'boo' => 'test', 'bam' => true])
                                        ->where('x = y'),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'UPDATE "sch"."foo" SET "bar" = \'\', "boo" = \'test\', "bam" = \'1\' WHERE x = y',
                        'prepare' => 'UPDATE "sch"."foo" SET "bar" = ?, "boo" = ?, "bam" = ? WHERE x = y',
                    ],
                ],
            ],
            [ // testCloneUpdate()
                'sqlObject' => function () {
                        $update1 = clone $this->update();
                        $update1->table('foo')
                                ->set(['bar' => 'baz'])
                                ->where('x = y');

                        $update2 = clone $this->update();
                        $update2->table('foo')
                            ->set(['bar' => 'baz'])
                            ->where([
                                'id = ?'=>1
                            ]);
                        return $update2;
                },
                'expected'  => [
                    'sql92' => [
                        'string'  => 'UPDATE "foo" SET "bar" = \'baz\' WHERE id = \'1\'',
                    ],
                ],
            ],
        ]);
    }
}
