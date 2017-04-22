<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl\Constraint;

use Zend\Db\Sql\Ddl\Constraint\PrimaryKey;

class PrimaryKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\PrimaryKey::getExpressionData
     */
    public function testGetExpressionData()
    {
        $pk = new PrimaryKey('foo');
        $this->assertEquals(
            [[
                'PRIMARY KEY (%s)',
                ['foo'],
                [$pk::TYPE_IDENTIFIER]
            ]],
            $pk->getExpressionData()
        );
    }
}
