<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Adapter;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\SelectableInterface;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class SelectBuilder extends AbstractSqlBuilder
{
    const SPECIFICATION_SELECT = 'select';
    const SPECIFICATION_JOINS  = 'joins';
    const SPECIFICATION_WHERE  = 'where';
    const SPECIFICATION_GROUP  = 'group';
    const SPECIFICATION_HAVING = 'having';
    const SPECIFICATION_ORDER  = 'order';
    const SPECIFICATION_LIMIT  = 'limit';
    const SPECIFICATION_OFFSET = 'offset';
    const SPECIFICATION_COMBINE = 'combine';

    protected $specifications = [
        'statementStart' => '%1$s',
        self::SPECIFICATION_SELECT => [
            'SELECT %1$s FROM %2$s' => [
                [1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '],
                null
            ],
            'SELECT %1$s %2$s FROM %3$s' => [
                null,
                [1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '],
                null
            ],
            'SELECT %1$s' => [
                [1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '],
            ],
        ],
        self::SPECIFICATION_JOINS  => [
            '%1$s' => [
                [3 => '%1$s JOIN %2$s ON %3$s', 'combinedby' => ' ']
            ]
        ],
        self::SPECIFICATION_WHERE  => 'WHERE %1$s',
        self::SPECIFICATION_GROUP  => [
            'GROUP BY %1$s' => [
                [1 => '%1$s', 'combinedby' => ', ']
            ]
        ],
        self::SPECIFICATION_HAVING => 'HAVING %1$s',
        self::SPECIFICATION_ORDER  => [
            'ORDER BY %1$s' => [
                [1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ']
            ]
        ],
        self::SPECIFICATION_LIMIT  => 'LIMIT %1$s',
        self::SPECIFICATION_OFFSET => 'OFFSET %1$s',
        'statementEnd' => '%1$s',
        self::SPECIFICATION_COMBINE => '%1$s ( %2$s )',
    ];

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_StatementStart($sqlObject, Context $context)
    {
        if ($sqlObject->combine !== []) {
            return ['('];
        }
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_StatementEnd($sqlObject, Context $context)
    {
        if ($sqlObject->combine !== []) {
            return [')'];
        }
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Select(Select $sqlObject, Context $context)
    {
        list($table, $fromTable) = $this->resolveTable($sqlObject->table, $context, $sqlObject);
        // build_ table columns
        $columns = [];
        foreach ($sqlObject->columns as $columnIndexOrAs => $column) {
            if ($column === Select::SQL_STAR) {
                $columns[] = [$fromTable . Select::SQL_STAR];
                continue;
            }

            $columnName = $this->resolveColumnValue(
                [
                    'column'       => $column,
                    'fromTable'    => $fromTable,
                    'isIdentifier' => true,
                ],
                $context
            );
            // build_ As portion
            if (is_string($columnIndexOrAs)) {
                $columnAs = $context->getPlatform()->quoteIdentifier($columnIndexOrAs);
            } elseif (stripos($columnName, ' as ') === false) {
                $columnAs = (is_string($column)) ? $context->getPlatform()->quoteIdentifier($column) : $context->getNestedAlias('column');
            }
            $columns[] = (isset($columnAs)) ? [$columnName, $columnAs] : [$columnName];
        }

        // build_ join columns
        foreach ($sqlObject->joins as $join) {
            $joinName = parent::resolveTable(
                is_array($join['name']) ? key($join['name']) : $join['name'],
                $context
            );

            foreach ($join['columns'] as $jKey => $jColumn) {
                $jColumns = [];
                $jFromTable = is_scalar($jColumn)
                            ? $joinName . $context->getPlatform()->getIdentifierSeparator()
                            : '';
                $jColumns[] = $this->resolveColumnValue(
                    [
                        'column'       => $jColumn,
                        'fromTable'    => $jFromTable,
                        'isIdentifier' => true,
                    ],
                    $context
                );
                if (is_string($jKey)) {
                    $jColumns[] = $context->getPlatform()->quoteIdentifier($jKey);
                } elseif ($jColumn !== Select::SQL_STAR) {
                    $jColumns[] = $context->getPlatform()->quoteIdentifier($jColumn);
                }
                $columns[] = $jColumns;
            }
        }

        if ($quantifier = $sqlObject->quantifier) {
            $quantifier = ($quantifier instanceof ExpressionInterface)
                    ? $this->buildSqlString($quantifier, $context)
                    : $quantifier;
        }

        if (!isset($table)) {
            return [$columns];
        } elseif (isset($quantifier)) {
            return [$quantifier, $columns, $table];
        } else {
            return [$columns, $table];
        }
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Where(Select $sqlObject, Context $context)
    {
        if ($sqlObject->where->count() == 0) {
            return;
        }
        return [
            $this->buildSqlString($sqlObject->where, $context)
        ];
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Group(Select $sqlObject, Context $context)
    {
        if ($sqlObject->group === null) {
            return;
        }
        // build_ table columns
        $groups = [];
        foreach ($sqlObject->group as $column) {
            $groups[] = $this->resolveColumnValue(
                [
                    'column'       => $column,
                    'isIdentifier' => true,
                ],
                $context
            );
        }
        return [$groups];
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Having(Select $sqlObject, Context $context)
    {
        if ($sqlObject->having->count() == 0) {
            return;
        }
        return [
            $this->buildSqlString($sqlObject->having, $context)
        ];
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Order(Select $sqlObject, Context $context)
    {
        if (!$sqlObject->order) {
            return;
        }
        $orders = [];
        foreach ($sqlObject->order as $k => $v) {
            if ($v instanceof ExpressionInterface) {
                $orders[] = [
                    $this->buildSqlString($v, $context)
                ];
                continue;
            }
            if (is_int($k)) {
                if (strpos($v, ' ') !== false) {
                    list($k, $v) = preg_split('# #', $v, 2);
                } else {
                    $k = $v;
                    $v = Select::ORDER_ASCENDING;
                }
            }
            if (strtoupper($v) == Select::ORDER_DESCENDING) {
                $orders[] = [$context->getPlatform()->quoteIdentifierInFragment($k), Select::ORDER_DESCENDING];
            } else {
                $orders[] = [$context->getPlatform()->quoteIdentifierInFragment($k), Select::ORDER_ASCENDING];
            }
        }
        return [$orders];
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Limit(Select $sqlObject, Context $context)
    {
        $limit = $sqlObject->limit;
        if ($limit === null) {
            return;
        }
        if ($context->getParameterContainer()) {
            $context->getParameterContainer()->offsetSet('limit', $limit, Adapter\ParameterContainer::TYPE_INTEGER);
            return [$context->getDriver()->formatParameterName('limit')];
        }
        return [$context->getPlatform()->quoteValue($limit)];
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Offset(Select $sqlObject, Context $context)
    {
        $offset = $sqlObject->offset;
        if ($offset === null) {
            return;
        }
        if ($context->getParameterContainer()) {
            $context->getParameterContainer()->offsetSet('offset', $offset, Adapter\ParameterContainer::TYPE_INTEGER);
            return [$context->getDriver()->formatParameterName('offset')];
        }

        return [$context->getPlatform()->quoteValue($offset)];
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Combine(Select $sqlObject, Context $context)
    {
        $COMBINE = $sqlObject->combine;
        if ($COMBINE == []) {
            return;
        }

        $type = $COMBINE['type'];
        if ($COMBINE['modifier']) {
            $type .= ' ' . $COMBINE['modifier'];
        }

        return [
            strtoupper($type),
            $this->buildSubSelect($COMBINE['select'], $context),
        ];
    }

    /**
     * @param array|string|TableIdentifier|Select $table
     * @param Context $context
     * @param mixed $object
     * @return array
     */
    protected function resolveTable($table, Context $context, $object = null)
    {
        $alias = null;

        if (is_array($table)) {
            $alias = key($table);
            $table = current($table);
        }

        $table = parent::resolveTable($table, $context);

        if ($alias) {
            $fromTable = $context->getPlatform()->quoteIdentifier($alias);
            $table = $this->renderTable($table, $fromTable);
        } else {
            $fromTable = $table;
        }

        if ($object->prefixColumnsWithTable && $fromTable) {
            $fromTable .= $context->getPlatform()->getIdentifierSeparator();
        } else {
            $fromTable = '';
        }

        return [
            $table,
            $fromTable
        ];
    }

    /**
     * @param string $table
     * @param null|string $alias
     * @return string
     */
    protected function renderTable($table, $alias = null)
    {
        return $table . ($alias ? ' AS ' . $alias : '');
    }
}
