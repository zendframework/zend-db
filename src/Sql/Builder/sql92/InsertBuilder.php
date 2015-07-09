<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class InsertBuilder extends AbstractSqlBuilder
{
    const SPECIFICATION_INSERT = 'insert';
    const SPECIFICATION_SELECT = 'select';
    /**
     * @var array Specification array
     */
    protected $specifications = [
        self::SPECIFICATION_INSERT => 'INSERT INTO %1$s (%2$s) VALUES (%3$s)',
        self::SPECIFICATION_SELECT => 'INSERT INTO %1$s %2$s %3$s',
    ];

    /**
     * @param Insert $sqlObject
     * @param Context $context
     * @return null|string
     * @throws Exception\InvalidArgumentException
     */
    protected function build_Insert(Insert $sqlObject, Context $context)
    {
        if ($sqlObject->select) {
            return;
        }

        if (!$sqlObject->columns) {
            throw new Exception\InvalidArgumentException('values or select should be present');
        }

        $columns = [];
        $values  = [];
        foreach (array_combine($sqlObject->columns, $sqlObject->values) as $column=>$value) {
            $columns[] = $context->getPlatform()->quoteIdentifier($column);
            if (is_scalar($value) && $context->getParameterContainer()) {
                $values[] = $context->getDriver()->formatParameterName($column);
                $context->getParameterContainer()->offsetSet($column, $value);
            } else {
                $values[] = $this->resolveColumnValue($value, $context);
            }
        }

        return sprintf(
            $this->specifications[self::SPECIFICATION_INSERT],
            $this->resolveTable($sqlObject->table, $context),
            implode(', ', $columns),
            implode(', ', $values)
        );
    }

    /**
     * @param Insert $sqlObject
     * @param Context $context
     * @return null|array
     */
    protected function build_Select(Insert $sqlObject, Context $context)
    {
        if (!$sqlObject->select) {
            return;
        }
        $selectSql = $this->buildSubSelect($sqlObject->select, $context);

        $columns = array_map([$context->getPlatform(), 'quoteIdentifier'], $sqlObject->columns);
        $columns = implode(', ', $columns);

        return sprintf(
            $this->specifications[self::SPECIFICATION_SELECT],
            $this->resolveTable($sqlObject->table, $context),
            $columns ? "($columns)" : "",
            $selectSql
        );
    }
}
