<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Ddl\Column;

use Zend\Db\Sql\Builder\Builder;
use Zend\Db\Sql\Builder\sql92\Ddl\Column as ColumnBuilder;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class ColumnBuilderTest extends AbstractTestCase
{
    protected $context;

    public function setUp()
    {
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testColumn()
    {
        $builder = new ColumnBuilder\ColumnBuilder(new Builder);
        $column  = new Column\Column;
        $column->setName('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',     $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('INTEGER', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );

        $column->setNullable(true);
        $this->assertEquals(
            [[
                '%s %s',
                [
                    new ExpressionParameter('foo',     $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('INTEGER', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );

        $column->setDefault('bar');
        $this->assertEquals(
            [[
                '%s %s DEFAULT %s',
                [
                    new ExpressionParameter('foo', $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('INTEGER', $column::TYPE_LITERAL),
                    new ExpressionParameter('bar', $column::TYPE_VALUE),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testAbstractLengthColumn()
    {
        $builder = new ColumnBuilder\AbstractLengthColumnBuilder(new Builder);
        $column  = $this->getMockForAbstractClass('Zend\Db\Sql\Ddl\Column\AbstractLengthColumn', [
            'foo', 4
        ]);

        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',        $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('INTEGER(4)', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testAbstractPrecisionColumn()
    {
        $builder = new ColumnBuilder\AbstractPrecisionColumnBuilder(new Builder);
        $column  = $this->getMockForAbstractClass('Zend\Db\Sql\Ddl\Column\AbstractPrecisionColumn', [
            'foo', 10, 5
        ]);

        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',           $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('INTEGER(10,5)', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testBigInteger()
    {
        $builder = new ColumnBuilder\IntegerBuilder(new Builder);
        $column  = new Column\BigInteger('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo', $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('BIGINT', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testBinary()
    {
        $builder = new ColumnBuilder\AbstractLengthColumnBuilder(new Builder);
        $column  = new Column\Binary('foo', 10000000);
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo', $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('BINARY(10000000)', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testBlob()
    {
        $builder = new ColumnBuilder\AbstractLengthColumnBuilder(new Builder);
        $column  = new Column\Blob('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo', $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('BLOB', $column::TYPE_LITERAL),
                ]
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testBoolean()
    {
        $builder = new ColumnBuilder\ColumnBuilder(new Builder);
        $column  = new Column\Boolean('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo', $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('BOOLEAN', $column::TYPE_LITERAL),
                ]
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testChar()
    {
        $builder = new ColumnBuilder\AbstractLengthColumnBuilder(new Builder);
        $column  = new Column\Char('foo', 20);
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',      $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('CHAR(20)', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testDate()
    {
        $builder = new ColumnBuilder\ColumnBuilder(new Builder);
        $column  = new Column\Date('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',  $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('DATE', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testDatetime()
    {
        $builder = new ColumnBuilder\ColumnBuilder(new Builder);
        $column  = new Column\Datetime('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',      $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('DATETIME', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testDecimal()
    {
        $builder = new ColumnBuilder\AbstractPrecisionColumnBuilder(new Builder);
        $column  = new Column\Decimal('foo', 10, 5);
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',           $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('DECIMAL(10,5)', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testFloating()
    {
        $builder = new ColumnBuilder\AbstractPrecisionColumnBuilder(new Builder);
        $column  = new Column\Floating('foo', 10, 5);
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',         $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('FLOAT(10,5)', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testInteger()
    {
        $builder = new ColumnBuilder\IntegerBuilder(new Builder);
        $column  = new Column\Integer('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',     $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('INTEGER', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );

        $column = new Column\Integer('foo');
        $column->addConstraint(new Constraint\PrimaryKey());
        $this->assertEquals(
            [
                [
                    '%s %s NOT NULL',
                    [
                        new ExpressionParameter('foo',     $column::TYPE_IDENTIFIER),
                        new ExpressionParameter('INTEGER', $column::TYPE_LITERAL),
                    ],
                ],
                ' ',
                [
                    'PRIMARY KEY',
                    [],
                ]
            ],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testText()
    {
        $builder = new ColumnBuilder\AbstractLengthColumnBuilder(new Builder);
        $column  = new Column\Text('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',  $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('TEXT', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testTime()
    {
        $builder = new ColumnBuilder\ColumnBuilder(new Builder);
        $column  = new Column\Time('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo', $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('TIME', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testTimestamp()
    {
        $builder = new ColumnBuilder\AbstractTimestampColumnBuilder(new Builder);
        $column  = new Column\Timestamp('foo');
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',       $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('TIMESTAMP', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testVarbinary()
    {
        $builder = new ColumnBuilder\AbstractLengthColumnBuilder(new Builder);
        $column  = new Column\Varbinary('foo', 20);
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',           $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('VARBINARY(20)', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }

    public function testVarchar()
    {
        $builder = new ColumnBuilder\AbstractLengthColumnBuilder(new Builder);
        $column  = new Column\Varchar('foo', 20);
        $this->assertEquals(
            [[
                '%s %s NOT NULL',
                [
                    new ExpressionParameter('foo',         $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('VARCHAR(20)', $column::TYPE_LITERAL),
                ],
            ]],
            $builder->getExpressionData($column, $this->context)
        );

        $column->setDefault('bar');
        $this->assertEquals(
            [[
                '%s %s NOT NULL DEFAULT %s',
                [
                    new ExpressionParameter('foo',         $column::TYPE_IDENTIFIER),
                    new ExpressionParameter('VARCHAR(20)', $column::TYPE_LITERAL),
                    new ExpressionParameter('bar',         $column::TYPE_VALUE),
                ]
            ]],
            $builder->getExpressionData($column, $this->context)
        );
    }
}
