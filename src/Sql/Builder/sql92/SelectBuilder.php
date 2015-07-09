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
use Zend\Db\Sql\Select;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class SelectBuilder extends AbstractSqlBuilder
{
    protected $statementStartSpecification = '%1$s';
    protected $selectNoTableSpecification = [
        'byArgNumber' => [
            1 => [
                'forEach' => [
                    'byCount' => [
                        1 => '%1$s', 2 => '%1$s AS %2$s'
                    ],
                ],
                'implode' => ', ',
            ],
        ],
        'format' => 'SELECT %1$s',
    ];
    protected $selectColumnsTableSpecification = [
        'byArgNumber' => [
            1 => [
                'forEach' => [
                    'byCount' => [
                        1 => '%1$s', 2 => '%1$s AS %2$s'
                    ],
                ],
                'implode' => ', ',
            ],
            2 => [
                'byCount' => [
                    1 => '%1$s', 2 => '%1$s AS %2$s'
                ],
            ],
        ],
        'format' => 'SELECT %1$s FROM %2$s',
    ];
    protected $selectFullSpecification = [
        'byArgNumber' => [
            2 => [
                'forEach' => [
                    'byCount' => [
                        1 => '%1$s', 2 => '%1$s AS %2$s'
                    ],
                ],
                'implode' => ', ',
            ],
            3 => [
                'byCount' => [
                    1 => '%1$s', 2 => '%1$s AS %2$s'
                ],
            ],
        ],
        'format' => 'SELECT %1$s %2$s FROM %3$s',
    ];
    protected $joinsSpecification = [
        'forEach' => [
            'byArgNumber' => [
                2 => [
                    'byCount' => [
                        1 => '%1$s', 2 => '%1$s AS %2$s'
                    ],
                ],
            ],
            'format' => '%1$s JOIN %2$s ON %3$s',
        ],
        'implode' => ' ',
    ];
    protected $whereSpecification = 'WHERE %1$s';
    protected $groupSpecification = [
        'implode' => ', ',
        'format'  => 'GROUP BY %1$s',
    ];
    protected $havingSpecification = 'HAVING %1$s';
    protected $orderSpecification = [
        'forEach' => [
            'byCount' => [
                1 => '%1$s', 2 => '%1$s %2$s'
            ],
        ],
        'implode' => ', ',
        'format' => 'ORDER BY %1$s',
    ];
    protected $limitSpecification = 'LIMIT %1$s';
    protected $offsetSpecification = 'OFFSET %1$s';
    protected $statementEndSpecification = '%1$s';
    protected $combineSpecification = '%1$s ( %2$s )';

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array|null
     */
    public function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Select', __METHOD__);
        $sqls = [];
        $sqls['start']   = $this->build_StatementStart($sqlObject, $context, $sqls);
        $sqls['select']  = $this->build_Select($sqlObject, $context, $sqls);
        $sqls['joins']   = $this->build_Joins($sqlObject, $context, $sqls);
        $sqls['where']   = $this->build_Where($sqlObject, $context, $sqls);
        $sqls['group']   = $this->build_Group($sqlObject, $context, $sqls);
        $sqls['having']  = $this->build_Having($sqlObject, $context, $sqls);
        $sqls['order']   = $this->build_Order($sqlObject, $context, $sqls);
        $sqls['limit']   = $this->build_Limit($sqlObject, $context, $sqls);
        $sqls['offset']  = $this->build_Offset($sqlObject, $context, $sqls);
        $sqls['end']     = $this->build_StatementEnd($sqlObject, $context, $sqls);
        $sqls['combine'] = $this->build_Combine($sqlObject, $context, $sqls);
        return $sqls;
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return string|null
     */
    protected function build_StatementStart($sqlObject, Context $context)
    {
        if ($sqlObject->combine !== []) {
            return '(';
        }
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return string|null
     */
    protected function build_StatementEnd($sqlObject, Context $context)
    {
        if ($sqlObject->combine !== []) {
            return ')';
        }
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Select(Select $sqlObject, Context $context)
    {
        $table = $this->nornalizeTable($sqlObject->table, $context);
        $fromTable = ($sqlObject->prefixColumnsWithTable && $table['columnAlias'])
            ? $table['columnAlias'] . $context->getPlatform()->getIdentifierSeparator()
            : '';
        unset($table['columnAlias']);
        if (!$table['alias']) {
            unset($table['alias']);
        }
        if (!$table['name']) {
            $table = null;
        }

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
            $jTable = $this->nornalizeTable($join['name'], $context);
            $joinName = $jTable['columnAlias'];

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

        if (!$table) {
            return [
                'spec' => $this->selectNoTableSpecification,
                'params' => [
                    $columns
                ],
            ];
        } elseif ($sqlObject->quantifier) {
            return [
                'spec' => $this->selectFullSpecification,
                'params' => [
                    $sqlObject->quantifier,
                    $columns,
                    $table,
                ],
            ];
        } else {
            return [
                'spec' => $this->selectColumnsTableSpecification,
                'params' => [
                    $columns,
                    $table,
                ],
            ];
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
            'spec' => $this->whereSpecification,
            'params' => $sqlObject->where,
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
            $groups[] = is_scalar($column)
                    ? $context->getPlatform()->quoteIdentifierInFragment($column)
                    : $column;
        }
        return [
            'spec' => $this->groupSpecification,
            'params' => $groups,
        ];
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
            'spec' => $this->havingSpecification,
            'params' => $sqlObject->having,
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
                    $v
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
        return [
            'spec' => $this->orderSpecification,
            'params' => $orders,
        ];
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
            $limit = $context->getDriver()->formatParameterName('limit');
        } else {
            $limit = $context->getPlatform()->quoteValue($limit);
        }
        return [
            'spec' => $this->limitSpecification,
            'params' => $limit,
        ];
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
            $offset = $context->getDriver()->formatParameterName('offset');
        } else {
            $offset = $context->getPlatform()->quoteValue($offset);
        }
        return [
            'spec' => $this->offsetSpecification,
            'params' => $offset
        ];
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
            'spec' => $this->combineSpecification,
            'params' => [
                strtoupper($type),
                $COMBINE['select'],
            ],
        ];
    }
}
