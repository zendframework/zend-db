<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Postgresql\Ddl\Index;

use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Db\Sql\Ddl\Index\Index;

class IndexDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Platform\Postgresql\Ddl\Index\IndexDecorator::setSubject
     * @covers Zend\Db\Sql\Platform\Postgresql\Ddl\Index\IndexDecorator::setTable
     * @covers Zend\Db\Sql\Platform\Postgresql\Ddl\Index\IndexDecorator::getExpressionData
     */
    public function testSpecificationHasCreateOnTable()
    {
        $index = new Index();
        $index->setName('test_index');
        $index->setColumns(['test_column_one', 'test_column_two']);

        $postgresIndex = new IndexDecorator();
        $postgresIndex->setSubject($index);
        $postgresIndex->setTable('test_table'); // PostgreSQL must have table name to operate on, unlike other engines

        $expressionData = $postgresIndex->getExpressionData()[0];

        // [0] => specification
        // [1] => values
        // [2] => types
        $this->assertEquals('CREATE INDEX %s ON %s(%s, %s)', $expressionData[0]);
        $this->assertEquals(['test_index', 'test_table', 'test_column_one', 'test_column_two'], $expressionData[1]);
        $this->assertEquals(
            [Index::TYPE_IDENTIFIER, Index::TYPE_IDENTIFIER, Index::TYPE_IDENTIFIER, Index::TYPE_IDENTIFIER],
            $expressionData[2]
        );
    }

    public function testExceptionThrownIfNoTableSpecified()
    {
        $postgresIndex = new IndexDecorator();

        $this->setExpectedException(InvalidQueryException::class);
        $postgresIndex->getExpressionData();
    }
}
