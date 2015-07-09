<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\Combine;

class CombineBuilderTest extends AbstractTestCase
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
            [ // testGetSqlStringEmpty()
                'sqlObject' => $this->combine(),
                'expected'  => [
                    'sql92' => [
                        'string'  => '',
                        'prepare' => ''
                    ],
                ],
            ],
            [ // testGetSqlString()
                'sqlObject' => $this->combine()
                                    ->union($this->select('t1'))
                                    ->intersect($this->select('t2'))
                                    ->except($this->select('t3'))
                                    ->union($this->select('t4')),
                'expected'  => [
                    'sql92' => [
                        'string'     => '(SELECT "t1".* FROM "t1") INTERSECT (SELECT "t2".* FROM "t2") EXCEPT (SELECT "t3".* FROM "t3") UNION (SELECT "t4".* FROM "t4")',
                    ],
                ],
            ],
            [ // testGetSqlStringWithModifier()
                'sqlObject' => $this->combine()
                                    ->union($this->select('t1'))
                                    ->union($this->select('t2'), 'ALL'),
                'expected'  => [
                    'sql92' => [
                        'string'     => '(SELECT "t1".* FROM "t1") UNION ALL (SELECT "t2".* FROM "t2")',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->combine([
                                        [$this->select('t1')],
                                        [$this->select('t2'), Combine::COMBINE_INTERSECT, 'ALL'],
                                        [$this->select('t3'), Combine::COMBINE_EXCEPT],
                                    ]),
                'expected'  => [
                    'sql92' => [
                        'string'     => '(SELECT "t1".* FROM "t1") INTERSECT ALL (SELECT "t2".* FROM "t2") EXCEPT (SELECT "t3".* FROM "t3")',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->combine([
                                        $this->select('t1'),
                                        $this->select('t2'),
                                        $this->select('t3'),
                                    ]),
                'expected'  => [
                    'sql92' => [
                        'string'     => '(SELECT "t1".* FROM "t1") UNION (SELECT "t2".* FROM "t2") UNION (SELECT "t3".* FROM "t3")',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->combine([
                                        $this->select('t1')->where(['x1'=>10]),
                                        $this->select('t2')->where(['x2'=>20])
                                    ]),
                'expected'  => [
                    'sql92' => [
                        'string'     => '(SELECT "t1".* FROM "t1" WHERE "x1" = \'10\') UNION (SELECT "t2".* FROM "t2" WHERE "x2" = \'20\')',
                        'prepare' => '(SELECT "t1".* FROM "t1" WHERE "x1" = ?) UNION (SELECT "t2".* FROM "t2" WHERE "x2" = ?)'
                    ],
                ],
            ],
            [
                'sqlObject' => $this->combine($this->combine([
                                        $this->select('t1'),
                                        $this->select('t2'),
                                    ])),
                'expected'  => [
                    'sql92' => [
                        'string'  => '((SELECT "t1".* FROM "t1") UNION (SELECT "t2".* FROM "t2"))',
                        'prepare' => '((SELECT "t1".* FROM "t1") UNION (SELECT "t2".* FROM "t2"))'
                    ],
                ],
            ],
            [
                'sqlObject' => $this->combine($this->combine([
                                        $this->select('foo'),
                                        $this->select('foo'),
                                    ])),
                'expected'  => [
                    'sqlite' => [
                        'string'  => '((SELECT "foo".* FROM "foo") UNION (SELECT "foo".* FROM "foo"))',
                        'prepare' => '((SELECT "foo".* FROM "foo") UNION (SELECT "foo".* FROM "foo"))'
                    ],
                ],
            ],
        ]);
    }
}
