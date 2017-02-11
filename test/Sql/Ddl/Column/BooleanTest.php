<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column\Boolean;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Ddl\Column\Boolean::getExpressionData
     */
    public function testGetExpressionData()
    {
        $column = new Boolean('foo');
        $this->assertEquals(
            [['%s %s NOT NULL', ['foo', 'BOOLEAN'], [$column::TYPE_IDENTIFIER, $column::TYPE_LITERAL]]],
            $column->getExpressionData()
        );
    }

    /**
     * @covers Zend\Db\Sql\Ddl\Column\Boolean
     *
     * @group 6257
     */
    public function testIsAlwaysNotNullable()
    {
        $column = new Boolean('foo', true);

        $this->assertFalse($column->isNullable());

        $column->setNullable(true);

        $this->assertFalse($column->isNullable());
    }
}
