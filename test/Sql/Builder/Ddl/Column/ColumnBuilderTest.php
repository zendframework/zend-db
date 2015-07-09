<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Ddl\Column;

use ZendTest\Db\Sql\Builder\AbstractTestCase;

/**
 * @covers Zend\Db\Sql\Builder\sql92\Ddl\Column\ColumnBuilder
 */
class ColumnBuilderTest extends AbstractTestCase
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
            $this->dataProvider_Column(),
            $this->dataProvider_OtherColumns()
        );
    }

    public function dataProvider_Column()
    {
        return [
            [
                'sqlObject' => $this->column_Column()
                                        ->setName('foo'),
                'expected'  => [
                    'sql92' => '"foo" INTEGER NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Column()
                                        ->setName('foo')
                                        ->setNullable(true),
                'expected'  => [
                    'sql92' => '"foo" INTEGER',
                ],
            ],
            [
                'sqlObject' => $this->column_Column()
                                        ->setName('foo')
                                        ->setNullable(true)
                                        ->setDefault('bar'),
                'expected'  => [
                    'sql92' => '"foo" INTEGER DEFAULT \'bar\'',
                ],
            ],
        ];
    }

    public function dataProvider_OtherColumns()
    {
        return [
            [
                'sqlObject' => $this->getMockForAbstractClass('Zend\Db\Sql\Ddl\Column\AbstractLengthColumn', [
                    'foo', 4
                ]),
                'expected'  => [
                    'sql92' => '"foo" INTEGER(4) NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->getMockForAbstractClass('Zend\Db\Sql\Ddl\Column\AbstractPrecisionColumn', [
                    'foo', 10, 5
                ]),
                'expected'  => [
                    'sql92' => '"foo" INTEGER(10,5) NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_BigInteger('foo'),
                'expected'  => [
                    'sql92' => '"foo" BIGINT NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Binary('foo', 10000000),
                'expected'  => [
                    'sql92' => '"foo" BINARY(10000000) NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Blob('foo'),
                'expected'  => [
                    'sql92' => '"foo" BLOB NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Boolean('foo'),
                'expected'  => [
                    'sql92' => '"foo" BOOLEAN NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Char('foo', 20),
                'expected'  => [
                    'sql92' => '"foo" CHAR(20) NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Date('foo'),
                'expected'  => [
                    'sql92' => '"foo" DATE NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Datetime('foo'),
                'expected'  => [
                    'sql92' => '"foo" DATETIME NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Decimal('foo', 10, 5),
                'expected'  => [
                    'sql92' => '"foo" DECIMAL(10,5) NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Floating('foo', 10, 5),
                'expected'  => [
                    'sql92' => '"foo" FLOAT(10,5) NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Integer('foo'),
                'expected'  => [
                    'sql92' => '"foo" INTEGER NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Integer('foo')->addConstraint($this->constraint_PrimaryKey()),
                'expected'  => [
                    'sql92' => '"foo" INTEGER NOT NULL PRIMARY KEY',
                ],
            ],
            [
                'sqlObject' => $this->column_Text('foo'),
                'expected'  => [
                    'sql92' => '"foo" TEXT NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Time('foo'),
                'expected'  => [
                    'sql92' => '"foo" TIME NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Timestamp('foo'),
                'expected'  => [
                    'sql92' => '"foo" TIMESTAMP NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Varbinary('foo', 20),
                'expected'  => [
                    'sql92' => '"foo" VARBINARY(20) NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Varchar('foo', 20),
                'expected'  => [
                    'sql92' => '"foo" VARCHAR(20) NOT NULL',
                ],
            ],
            [
                'sqlObject' => $this->column_Varchar('foo', 20)->setDefault('bar'),
                'expected'  => [
                    'sql92' => '"foo" VARCHAR(20) NOT NULL DEFAULT \'bar\'',
                ],
            ],
        ];
    }
}
