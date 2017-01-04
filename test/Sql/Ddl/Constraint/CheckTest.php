<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl\Constraint;

use Zend\Db\Sql\Ddl\Constraint\Check;

class CheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Ddl\Constraint\Check::getExpressionData
     */
    public function testGetExpressionData()
    {
        $check = new Check('id>0', 'foo');
        $this->assertEquals(
            [[
                'CONSTRAINT %s CHECK (%s)',
                ['foo', 'id>0'],
                [$check::TYPE_IDENTIFIER, $check::TYPE_LITERAL]
            ]],
            $check->getExpressionData()
        );
    }
}
