<?php

/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column\Serial;
use Zend\Db\Sql\Ddl\Constraint\PrimaryKey;

class SerialTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExpressionData()
    {
        $column = new Serial('id');
        $this->assertEquals(
            [['%s %s NOT NULL', ['id', 'SERIAL'], [$column::TYPE_IDENTIFIER, $column::TYPE_LITERAL]]],
            $column->getExpressionData()
        );

        $column = new Serial('id');
        $column->addConstraint(new PrimaryKey());
        $this->assertEquals(
            [
                ['%s %s NOT NULL', ['id', 'SERIAL'], [$column::TYPE_IDENTIFIER, $column::TYPE_LITERAL]],
                ' ',
                ['PRIMARY KEY', [], []],
            ],
            $column->getExpressionData()
        );
    }
}
