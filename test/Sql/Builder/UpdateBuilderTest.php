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
use Zend\Db\Sql\Join;

/**
 * @covers Zend\Db\Sql\Builder\sql92\UpdateBuilder
 */
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
        return $this->prepareDataProvider(
            $this->dataProvider_Else(),
            $this->dataProvider_Set(),
            $this->dataProvider_SubSelectAndExpressions(),
            $this->dataProvider_Table(),
            $this->dataProvider_Where(),
            $this->dataProvider_Joins()
        );
    }

    public function dataProvider_Set()
    {
        return [
            [
                'sqlObject' => $this->update()
                                        ->table('foo')
                                        ->set([
                                            'bar'   => 'baz',
                                            'boo'   => new Expression('NOW()'),
                                            'bam'   => null,
                                            'false' => false,
                                            'true'  => true,
                                        ]),
                'expected'  => [
                    'sql92' => [
                        'string'     => 'UPDATE "foo" SET "bar" = \'baz\', "boo" = NOW(), "bam" = NULL, "false" = \'\', "true" = \'1\'',
                        'prepare'    => 'UPDATE "foo" SET "bar" = ?, "boo" = NOW(), "bam" = NULL, "false" = ?, "true" = ?',
                        'parameters' => ['bar' => 'baz', 'false' => false, 'true' => true],
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Table()
    {
        return [
            [
                'sqlObject' => $this->update('foo')->set(['bar' => 'baz']),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'UPDATE "foo" SET "bar" = \'baz\'',
                        'prepare' => 'UPDATE "foo" SET "bar" = ?',
                    ],
                ],
            ],
            [
                'sqlObject' => $this->update(new TableIdentifier('foo', 'sch'))->set(['bar' => 'baz']),
                'expected'  => [
                    'sql92' => [
                        'string'  => 'UPDATE "sch"."foo" SET "bar" = \'baz\'',
                        'prepare' => 'UPDATE "sch"."foo" SET "bar" = ?',
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_SubSelectAndExpressions()
    {
        return [
            [
                'sqlObject' => $this->update('foo')->set(['x'=>$this->select('foo')]),
                'expected'  => [
                    'sql92'     => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo")',
                    'MySql'     => 'UPDATE `foo` SET `x` = (SELECT `foo`.* FROM `foo`)',
                    'Oracle'    => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo")',
                    'SqlServer' => 'UPDATE [foo] SET [x] = (SELECT [foo].* FROM [foo])',
                ],
            ],
            [
                'sqlObject' => $this->update('foo')->set(['x'=>new Expression('?', [$this->select('foo')])]),
                'expected'  => [
                    'sql92'     => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo")',
                    'MySql'     => 'UPDATE `foo` SET `x` = (SELECT `foo`.* FROM `foo`)',
                    'Oracle'    => 'UPDATE "foo" SET "x" = (SELECT "foo".* FROM "foo")',
                    'SqlServer' => 'UPDATE [foo] SET [x] = (SELECT [foo].* FROM [foo])',
                ],
            ],
        ];
    }

    public function dataProvider_Where()
    {
        return [
            [
                'sqlObject' => $this->update('foo')->set(['bar' => 'baz'])->where(['x'=>'y']),
                'expected'  => [
                    'sql92'     => [
                        'string'     => 'UPDATE "foo" SET "bar" = \'baz\' WHERE "x" = \'y\'',
                        'prepare'    => 'UPDATE "foo" SET "bar" = ? WHERE "x" = ?',
                        'parameters' => ['bar' => 'baz', 'expr1' => 'y'],
                    ],
                    'MySql'     => [
                        'string'     => 'UPDATE `foo` SET `bar` = \'baz\' WHERE `x` = \'y\'',
                        'prepare'    => 'UPDATE `foo` SET `bar` = ? WHERE `x` = ?',
                        'parameters' => ['bar' => 'baz', 'expr1' => 'y'],
                    ],
                    'Oracle'    => [
                        'string'     => 'UPDATE "foo" SET "bar" = \'baz\' WHERE "x" = \'y\'',
                        'prepare'    => 'UPDATE "foo" SET "bar" = ? WHERE "x" = ?',
                        'parameters' => ['bar' => 'baz', 'expr1' => 'y'],
                    ],
                    'SqlServer' => [
                        'string'     => 'UPDATE [foo] SET [bar] = \'baz\' WHERE [x] = \'y\'',
                        'prepare'    => 'UPDATE [foo] SET [bar] = ? WHERE [x] = ?',
                        'parameters' => ['bar' => 'baz', 'expr1' => 'y'],
                    ],
                ],
            ],
        ];
    }

    public function dataProvider_Else()
    {
        return [
            'clone' => [ // testCloneUpdate()
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
        ];
    }

    public function dataProvider_Joins()
    {
        return [
            'Update::processJoins()_1' => [
                'sqlObject' => $this->update('foo')->set(['x' => 'y'])->where(['xx' => 'yy'])->join(
                    'bar',
                    'bar.barId = foo.barId'
                ),
                'expected'  => [
                    'sql92'     => [
                        'string' => 'UPDATE "foo" INNER JOIN "bar" ON "bar"."barId" = "foo"."barId" SET "x" = \'y\' WHERE "xx" = \'yy\'',
                    ],
                    'MySql'     => [
                        'string' => 'UPDATE `foo` INNER JOIN `bar` ON `bar`.`barId` = `foo`.`barId` SET `x` = \'y\' WHERE `xx` = \'yy\'',
                    ],
                    'Oracle'     => [
                        'string' => 'UPDATE "foo" INNER JOIN "bar" ON "bar"."barId" = "foo"."barId" SET "x" = \'y\' WHERE "xx" = \'yy\'',
                    ],
                    'SqlServer' => [
                        'string' => 'UPDATE [foo] INNER JOIN [bar] ON [bar].[barId] = [foo].[barId] SET [x] = \'y\' WHERE [xx] = \'yy\'',
                    ],
                ],
            ],
            'Update::processJoins()_2' => [
                'sqlObject' => $this->update('Document')->set(['x' => 'y'])
                                    ->join(
                                        'User', // table name
                                        'User.UserId = Document.UserId' // expression to join on (will be quoted by platform object before insertion),
                                        // default JOIN INNER
                                    )
                                    ->join(
                                        'Category',
                                        'Category.CategoryId = Document.CategoryId',
                                        Join::JOIN_LEFT // (optional), one of inner, outer, left, right
                                    ),
                'expected'  => [
                    'sql92'     => [
                        'string' => 'UPDATE "Document" INNER JOIN "User" ON "User"."UserId" = "Document"."UserId" LEFT JOIN "Category" ON "Category"."CategoryId" = "Document"."CategoryId" SET "x" = \'y\'',
                    ],
                    'MySql'     => [
                        'string' => 'UPDATE `Document` INNER JOIN `User` ON `User`.`UserId` = `Document`.`UserId` LEFT JOIN `Category` ON `Category`.`CategoryId` = `Document`.`CategoryId` SET `x` = \'y\'',
                    ],
                    'Oracle'     => [
                        'string' => 'UPDATE "Document" INNER JOIN "User" ON "User"."UserId" = "Document"."UserId" LEFT JOIN "Category" ON "Category"."CategoryId" = "Document"."CategoryId" SET "x" = \'y\'',
                    ],
                    'SqlServer' => [
                        'string' => 'UPDATE [Document] INNER JOIN [User] ON [User].[UserId] = [Document].[UserId] LEFT JOIN [Category] ON [Category].[CategoryId] = [Document].[CategoryId] SET [x] = \'y\'',
                    ],
                ],
            ],
        ];
    }
}
