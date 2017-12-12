<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl\Column;

use PHPUnit\Framework\TestCase;
use Zend\Db\Sql\Ddl\Column\AbstractLengthColumnInterface;

class AbstractLengthColumnTest extends TestCase
{
    /**
     * @covers \Zend\Db\Sql\Ddl\Column\AbstractLengthColumn::setLength
     */
    public function testSetLength()
    {
        $column = $this->getMockForAbstractClass('Zend\Db\Sql\Ddl\Column\AbstractLengthColumn', ['foo', 55]);
        self::assertEquals(55, $column->getLength());
        self::assertSame($column, $column->setLength(20));
        self::assertEquals(20, $column->getLength());
    }

    /**
     * @covers \Zend\Db\Sql\Ddl\Column\AbstractLengthColumn::getLength
     */
    public function testGetLength()
    {
        $column = $this->getMockForAbstractClass('Zend\Db\Sql\Ddl\Column\AbstractLengthColumn', ['foo', 55]);
        self::assertEquals(55, $column->getLength());
    }

    /**
     * @covers \Zend\Db\Sql\Ddl\Column\AbstractLengthColumn::getExpressionData
     */
    public function testGetExpressionData()
    {
        $column = $this->getMockForAbstractClass('Zend\Db\Sql\Ddl\Column\AbstractLengthColumn', ['foo', 4]);

        self::assertEquals(
            [['%s %s NOT NULL', ['foo', 'INTEGER(4)'], [$column::TYPE_IDENTIFIER, $column::TYPE_LITERAL]]],
            $column->getExpressionData()
        );
    }

    /**
     * @covers \Zend\Db\Sql\Ddl\Column\AbstractLengthColumn::enableMultibyte
     * @covers \Zend\Db\Sql\Ddl\Column\AbstractLengthColumn::disableMultibyte
     * @covers \Zend\Db\Sql\Ddl\Column\AbstractLengthColumn::isMultibyte
     */
    public function testToggleMultibyteOption()
    {
        /** @var AbstractLengthColumnInterface $column */
        $column = $this->getMockForAbstractClass(
            'Zend\Db\Sql\Ddl\Column\AbstractLengthColumn',
            ['foo', 4]
        );
        self::assertFalse($column->isMultibyte());
        $column->enableMultibyte();
        self::assertTrue($column->isMultibyte());
        $column->disableMultibyte();
        self::assertFalse($column->isMultibyte());
    }
}
