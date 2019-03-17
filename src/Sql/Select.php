<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;

/**
 * @property Where $where
 * @property Having $having
 */
class Select extends AbstractPreparableSql
{
    /**#@+
     * Constant
     * @const
     */
    public const SELECT = 'select';
    public const QUANTIFIER = 'quantifier';
    public const COLUMNS = 'columns';
    public const TABLE = 'table';
    public const JOINS = 'joins';
    public const WHERE = 'where';
    public const GROUP = 'group';
    public const HAVING = 'having';
    public const ORDER = 'order';
    public const LIMIT = 'limit';
    public const OFFSET = 'offset';
    public const QUANTIFIER_DISTINCT = 'DISTINCT';
    public const QUANTIFIER_ALL = 'ALL';
    public const JOIN_INNER = Join::JOIN_INNER;
    public const JOIN_OUTER = Join::JOIN_OUTER;
    public const JOIN_LEFT = Join::JOIN_LEFT;
    public const JOIN_RIGHT = Join::JOIN_RIGHT;
    public const JOIN_RIGHT_OUTER = Join::JOIN_RIGHT_OUTER;
    public const JOIN_LEFT_OUTER  = Join::JOIN_LEFT_OUTER;
    public const SQL_STAR = '*';
    public const ORDER_ASCENDING = 'ASC';
    public const ORDER_DESCENDING = 'DESC';
    public const COMBINE = 'combine';
    public const COMBINE_UNION = 'union';
    public const COMBINE_EXCEPT = 'except';
    public const COMBINE_INTERSECT = 'intersect';
    /**#@-*/

    /**
     * @deprecated use JOIN_LEFT_OUTER instead
     */
    public const JOIN_OUTER_LEFT  = 'outer left';

    /**
     * @deprecated use JOIN_LEFT_OUTER instead
     */
    public const JOIN_OUTER_RIGHT = 'outer right';

    /** @var array Specifications */
    protected $specifications = [
        'statementStart' => '%1$s',
        self::SELECT => [
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
        self::JOINS  => [
            '%1$s' => [
                [3 => '%1$s JOIN %2$s ON %3$s', 'combinedby' => ' ']
            ]
        ],
        self::WHERE  => 'WHERE %1$s',
        self::GROUP  => [
            'GROUP BY %1$s' => [
                [1 => '%1$s', 'combinedby' => ', ']
            ]
        ],
        self::HAVING => 'HAVING %1$s',
        self::ORDER  => [
            'ORDER BY %1$s' => [
                [1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ']
            ]
        ],
        self::LIMIT  => 'LIMIT %1$s',
        self::OFFSET => 'OFFSET %1$s',
        'statementEnd' => '%1$s',
        self::COMBINE => '%1$s ( %2$s )',
    ];

    /** @var bool */
    protected $tableReadOnly = false;

    /** @var bool */
    protected $prefixColumnsWithTable = true;

    /** @var string|array|TableIdentifier */
    protected $table;

    /** @var null|string|Expression */
    protected $quantifier;

    /** @var array */
    protected $columns = [self::SQL_STAR];

    /** @var null|Join */
    protected $joins;

    /** @var Where */
    protected $where;

    /** @var array */
    protected $order = [];

    /** @var null|array */
    protected $group;

    /** @var null|string|array */
    protected $having;

    /** @var int|null */
    protected $limit;

    /** @var int|null */
    protected $offset;

    /** @var array */
    protected $combine = [];

    /**
     * Constructor
     *
     * @param null|string|array|TableIdentifier $table
     */
    public function __construct(?$table = null)
    {
        if ($table) {
            $this->from($table);
            $this->tableReadOnly = true;
        }

        $this->where = new Where;
        $this->joins = new Join;
        $this->having = new Having;
    }

    /**
     * Create from clause
     *
     * @param string|array|TableIdentifier $table
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function from($table) : self
    {
        if ($this->tableReadOnly) {
            throw new Exception\InvalidArgumentException(
                'Since this object was created with a table and/or schema in the constructor, it is read only.'
            );
        }

        if (! is_string($table) && ! is_array($table) && ! $table instanceof TableIdentifier) {
            throw new Exception\InvalidArgumentException(
                '$table must be a string, array, or an instance of TableIdentifier'
            );
        }

        if (is_array($table) && (! is_string(key($table)) || count($table) !== 1)) {
            throw new Exception\InvalidArgumentException(
                'from() expects $table as an array is a single element associative array'
            );
        }

        $this->table = $table;
        return $this;
    }

    /**
     * @param string|Expression $quantifier DISTINCT|ALL
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function quantifier($quantifier) : self
    {
        if (! is_string($quantifier) && ! $quantifier instanceof ExpressionInterface) {
            throw new Exception\InvalidArgumentException(
                'Quantifier must be one of DISTINCT, ALL, or some platform specific object implementing '
                . 'ExpressionInterface'
            );
        }
        $this->quantifier = $quantifier;
        return $this;
    }

    /**
     * Specify columns from which to select
     *
     * Possible valid states:
     *
     *   array(*)
     *
     *   array(value, ...)
     *     value can be strings or Expression objects
     *
     *   array(string => value, ...)
     *     key string will be use as alias,
     *     value can be string or Expression objects
     *
     * @param array $columns
     * @param bool  $prefixColumnsWithTable
     * @return self
     */
    public function columns(array $columns, bool $prefixColumnsWithTable = true) : self
    {
        $this->columns = $columns;
        $this->prefixColumnsWithTable = $prefixColumnsWithTable;
        return $this;
    }

    /**
     * Create join clause
     *
     * @param string|array|TableIdentifier $name
     * @param string|Predicate\Expression $on
     * @param string|array $columns
     * @param string $type one of the JOIN_* constants
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function join($name, $on, $columns = self::SQL_STAR, string $type = self::JOIN_INNER) : self
    {
        $this->joins->join($name, $on, $columns, $type);

        return $this;
    }

    /**
     * Create where clause
     *
     * @param Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param string $combination One of the OP_* constants from Predicate\PredicateSet
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function where($predicate, string $combination = Predicate\PredicateSet::OP_AND) : self
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param mixed $group
     * @return self
     */
    public function group($group) : self
    {
        if (is_array($group)) {
            foreach ($group as $o) {
                $this->group[] = $o;
            }
        } else {
            $this->group[] = $group;
        }
        return $this;
    }

    /**
     * Create having clause
     *
     * @param Where|\Closure|string|array $predicate
     * @param string $combination One of the OP_* constants from Predicate\PredicateSet
     * @return self
     */
    public function having($predicate, string $combination = Predicate\PredicateSet::OP_AND) : self
    {
        if ($predicate instanceof Having) {
            $this->having = $predicate;
        } else {
            $this->having->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param string|array|Expression $order
     * @return self Provides a fluent interface
     */
    public function order($order) : self
    {
        if (is_string($order)) {
            if (strpos($order, ',') !== false) {
                $order = preg_split('#,\s+#', $order);
            } else {
                $order = (array) $order;
            }
        } elseif (! is_array($order)) {
            $order = [$order];
        }
        foreach ($order as $k => $v) {
            if (is_string($k)) {
                $this->order[$k] = $v;
            } else {
                $this->order[] = $v;
            }
        }
        return $this;
    }

    public function limit(int $limit) : self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset) : self
    {
        $this->offset = $offset;
        return $this;
    }

    public function combine(Select $select, string $type = self::COMBINE_UNION, string $modifier = '') : self
    {
        if ($this->combine !== []) {
            throw new Exception\InvalidArgumentException(
                'This Select object is already combined and cannot be combined with multiple Selects objects'
            );
        }
        $this->combine = compact('select', 'type', 'modifier');
        return $this;
    }

    public function reset(string $part) : self
    {
        switch ($part) {
            case self::TABLE:
                if ($this->tableReadOnly) {
                    throw new Exception\InvalidArgumentException(
                        'Since this object was created with a table and/or schema in the constructor, it is read only.'
                    );
                }
                $this->table = null;
                break;
            case self::QUANTIFIER:
                $this->quantifier = null;
                break;
            case self::COLUMNS:
                $this->columns = [];
                break;
            case self::JOINS:
                $this->joins = new Join;
                break;
            case self::WHERE:
                $this->where = new Where;
                break;
            case self::GROUP:
                $this->group = null;
                break;
            case self::HAVING:
                $this->having = new Having;
                break;
            case self::LIMIT:
                $this->limit = null;
                break;
            case self::OFFSET:
                $this->offset = null;
                break;
            case self::ORDER:
                $this->order = [];
                break;
            case self::COMBINE:
                $this->combine = [];
                break;
        }
        return $this;
    }

    /**
     * @param string $index
     * @param array|string $specification
     * @return self
     */
    public function setSpecification(string $index, $specification) : self
    {
        if (! method_exists($this, 'process' . $index)) {
            throw new Exception\InvalidArgumentException('Not a valid specification name.');
        }
        $this->specifications[$index] = $specification;
        return $this;
    }

    public function getRawState(?string $key = null)
    {
        $rawState = [
            self::TABLE      => $this->table,
            self::QUANTIFIER => $this->quantifier,
            self::COLUMNS    => $this->columns,
            self::JOINS      => $this->joins,
            self::WHERE      => $this->where,
            self::ORDER      => $this->order,
            self::GROUP      => $this->group,
            self::HAVING     => $this->having,
            self::LIMIT      => $this->limit,
            self::OFFSET     => $this->offset,
            self::COMBINE    => $this->combine
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    public function isTableReadOnly() : bool
    {
        return $this->tableReadOnly;
    }

    protected function processStatementStart(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) : ?array {
        if ($this->combine !== []) {
            return ['('];
        }
    }

    protected function processStatementEnd(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) : ?array {
        if ($this->combine !== []) {
            return [')'];
        }
    }

    protected function processSelect(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) : ?array {
        $expr = 1;

        [$table, $fromTable] = $this->resolveTable($this->table, $platform, $driver, $parameterContainer);
        // process table columns
        $columns = [];
        foreach ($this->columns as $columnIndexOrAs => $column) {
            if ($column === self::SQL_STAR) {
                $columns[] = [$fromTable . self::SQL_STAR];
                continue;
            }

            $columnName = $this->resolveColumnValue(
                [
                    'column'       => $column,
                    'fromTable'    => $fromTable,
                    'isIdentifier' => true,
                ],
                $platform,
                $driver,
                $parameterContainer,
                (is_string($columnIndexOrAs) ? $columnIndexOrAs : 'column')
            );
            // process As portion
            if (is_string($columnIndexOrAs)) {
                $columnAs = $platform->quoteIdentifier($columnIndexOrAs);
            } elseif (stripos($columnName, ' as ') === false) {
                $columnAs = is_string($column) ? $platform->quoteIdentifier($column) : 'Expression' . $expr++;
            }
            $columns[] = isset($columnAs) ? [$columnName, $columnAs] : [$columnName];
        }

        // process join columns
        foreach ($this->joins->getJoins() as $join) {
            $joinName = is_array($join['name']) ? key($join['name']) : $join['name'];
            $joinName = parent::resolveTable($joinName, $platform, $driver, $parameterContainer);

            foreach ($join['columns'] as $jKey => $jColumn) {
                $jColumns = [];
                $jFromTable = is_scalar($jColumn)
                            ? $joinName . $platform->getIdentifierSeparator()
                            : '';
                $jColumns[] = $this->resolveColumnValue(
                    [
                        'column'       => $jColumn,
                        'fromTable'    => $jFromTable,
                        'isIdentifier' => true,
                    ],
                    $platform,
                    $driver,
                    $parameterContainer,
                    (is_string($jKey) ? $jKey : 'column')
                );
                if (is_string($jKey)) {
                    $jColumns[] = $platform->quoteIdentifier($jKey);
                } elseif ($jColumn !== self::SQL_STAR) {
                    $jColumns[] = $platform->quoteIdentifier($jColumn);
                }
                $columns[] = $jColumns;
            }
        }

        if ($this->quantifier) {
            $quantifier = ($this->quantifier instanceof ExpressionInterface)
                    ? $this->processExpression($this->quantifier, $platform, $driver, $parameterContainer, 'quantifier')
                    : $this->quantifier;
        }

        if (! isset($table)) {
            return [$columns];
        }

        if (isset($quantifier)) {
            return [$quantifier, $columns, $table];
        }

        return [$columns, $table];
    }

    protected function processJoins(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        return $this->processJoin($this->joins, $platform, $driver, $parameterContainer);
    }

    protected function processWhere(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if ($this->where->count() == 0) {
            return;
        }
        return [
            $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where')
        ];
    }

    protected function processGroup(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if ($this->group === null) {
            return;
        }
        // process table columns
        $groups = [];
        foreach ($this->group as $column) {
            $groups[] = $this->resolveColumnValue(
                [
                    'column'       => $column,
                    'isIdentifier' => true,
                ],
                $platform,
                $driver,
                $parameterContainer,
                'group'
            );
        }
        return [$groups];
    }

    protected function processHaving(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if ($this->having->count() == 0) {
            return;
        }
        return [
            $this->processExpression($this->having, $platform, $driver, $parameterContainer, 'having')
        ];
    }

    protected function processOrder(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) : ?array {
        if (empty($this->order)) {
            return null;
        }
        $orders = [];
        foreach ($this->order as $k => $v) {
            if ($v instanceof ExpressionInterface) {
                $orders[] = [
                    $this->processExpression($v, $platform, $driver, $parameterContainer)
                ];
                continue;
            }
            if (is_int($k)) {
                if (strpos($v, ' ') !== false) {
                    [$k, $v] = preg_split('# #', $v, 2);
                } else {
                    $k = $v;
                    $v = self::ORDER_ASCENDING;
                }
            }
            if (strcasecmp(trim($v), self::ORDER_DESCENDING) === 0) {
                $orders[] = [$platform->quoteIdentifierInFragment($k), self::ORDER_DESCENDING];
            } else {
                $orders[] = [$platform->quoteIdentifierInFragment($k), self::ORDER_ASCENDING];
            }
        }
        return [$orders];
    }

    protected function processLimit(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if ($this->limit === null) {
            return;
        }
        if ($parameterContainer) {
            $paramPrefix = $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName($paramPrefix . 'limit')];
        }
        return [$platform->quoteValue($this->limit)];
    }

    protected function processOffset(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if ($this->offset === null) {
            return;
        }
        if ($parameterContainer) {
            $paramPrefix = $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName($paramPrefix . 'offset')];
        }

        return [$platform->quoteValue($this->offset)];
    }

    protected function processCombine(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if ($this->combine == []) {
            return;
        }

        $type = $this->combine['type'];
        if ($this->combine['modifier']) {
            $type .= ' ' . $this->combine['modifier'];
        }

        return [
            strtoupper($type),
            $this->processSubSelect($this->combine['select'], $platform, $driver, $parameterContainer),
        ];
    }

    public function __get(string $name)
    {
        switch (strtolower($name)) {
            case 'where':
                return $this->where;
            case 'having':
                return $this->having;
            case 'joins':
                return $this->joins;
            default:
                throw new Exception\InvalidArgumentException('Not a valid magic property for this object');
        }
    }

    public function __clone()
    {
        $this->where  = clone $this->where;
        $this->joins  = clone $this->joins;
        $this->having = clone $this->having;
    }

    /**
     * @param string|TableIdentifier|Select $table
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @return array
     */
    protected function resolveTable(
        $table,
        PlatformInterface  $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        $alias = null;

        if (is_array($table)) {
            $alias = key($table);
            $table = current($table);
        }

        $table = parent::resolveTable($table, $platform, $driver, $parameterContainer);

        if ($alias) {
            $fromTable = $platform->quoteIdentifier($alias);
            $table = $this->renderTable($table, $fromTable);
        } else {
            $fromTable = $table;
        }

        if ($this->prefixColumnsWithTable && $fromTable) {
            $fromTable .= $platform->getIdentifierSeparator();
        } else {
            $fromTable = '';
        }

        return [
            $table,
            $fromTable
        ];
    }
}
