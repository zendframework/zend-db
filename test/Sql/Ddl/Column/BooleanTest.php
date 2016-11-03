<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column\Boolean;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Ddl\Column\Boolean
     *
     * @group 6257
     */
    public function testSetNullable()
    {
        $column = new Boolean('foo', true);
        $this->assertTrue($column->isNullable());

        $column = new Boolean('foo', false);
        $this->assertFalse($column->isNullable());

        $column->setNullable(true);
        $this->assertTrue($column->isNullable());

        $column->setNullable(false);
        $this->assertFalse($column->isNullable());
    }
}
