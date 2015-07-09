<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

/**
 *
 * @property null|string|array|TableIdentifier $table
 * @property string|Expression $quantifier DISTINCT|ALL
 * @property array $columns
 * @property $joins
 * @property Where $where
 * @property string|array $order
 * @property $group
 * @property Having $having
 * @property int $limit
 * @property int $offset
 * @property $combine
 * @property $prefixColumnsWithTable
 */
class Select extends AbstractSqlObject implements PreparableSqlObjectInterface, SelectableInterface
{
    const QUANTIFIER_DISTINCT = 'DISTINCT';
    const QUANTIFIER_ALL = 'ALL';
    const JOIN_INNER = Join::JOIN_INNER;
    const JOIN_OUTER = Join::JOIN_OUTER;
    const JOIN_LEFT = Join::JOIN_LEFT;
    const JOIN_RIGHT = Join::JOIN_RIGHT;
    const JOIN_RIGHT_OUTER = Join::JOIN_RIGHT_OUTER;
    const JOIN_LEFT_OUTER  = Join::JOIN_LEFT_OUTER;
    const SQL_STAR = '*';
    const ORDER_ASCENDING = 'ASC';
    const ORDER_DESCENDING = 'DESC';
    const COMBINE = 'combine';
    const COMBINE_UNION = 'union';
    const COMBINE_EXCEPT = 'except';
    const COMBINE_INTERSECT = 'intersect';

    /**
     * @var array Specifications
     */

    /**
     * @var bool
     */
    protected $tableReadOnly = false;

    /**
     * @var bool
     */
    protected $prefixColumnsWithTable = true;

    /**
     * @var string|array|TableIdentifier
     */
    protected $table = null;

    /**
     * @var null|string|Expression
     */
    protected $quantifier = null;

    /**
     * @var array
     */
    protected $columns = [self::SQL_STAR];

    /**
     * @var null|Join
     */
    protected $joins = null;

    /**
     * @var Where
     */
    protected $where = null;

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var null|array
     */
    protected $group = null;

    /**
     * @var null|string|array
     */
    protected $having = null;

    /**
     * @var int|null
     */
    protected $limit = null;

    /**
     * @var int|null
     */
    protected $offset = null;

    protected $__getProperties = [
        'table',
        'quantifier',
        'columns',
        'joins',
        'where',
        'order',
        'group',
        'having',
        'limit',
        'offset',
        'combine',
        'prefixColumnsWithTable',
    ];
    /**
     * @var array
     */
    protected $combine = [];

    /**
     * Constructor
     *
     * @param  null|string|array|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        parent::__construct();
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
     * @param  string|array|TableIdentifier $table
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function from($table)
    {
        if ($this->tableReadOnly) {
            throw new Exception\InvalidArgumentException('Since this object was created with a table and/or schema in the constructor, it is read only.');
        }

        if (!is_string($table) && !is_array($table) && !$table instanceof TableIdentifier) {
            throw new Exception\InvalidArgumentException('$table must be a string, array, or an instance of TableIdentifier');
        }

        if (is_array($table) && (!is_string(key($table)) || count($table) !== 1)) {
            throw new Exception\InvalidArgumentException('from() expects $table as an array is a single element associative array');
        }

        $this->table = $table;
        return $this;
    }

    /**
     * @param string|ExpressionInterface $quantifier DISTINCT|ALL
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function quantifier($quantifier)
    {
        if (!is_string($quantifier) && !$quantifier instanceof ExpressionInterface) {
            throw new Exception\InvalidArgumentException(
                'Quantifier must be one of DISTINCT, ALL, or some platform specific object implementing ExpressionInterface'
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
     * @param  array $columns
     * @param  bool  $prefixColumnsWithTable
     * @return self
     */
    public function columns(array $columns, $prefixColumnsWithTable = true)
    {
        $this->columns = $columns;
        $this->prefixColumnsWithTable = (bool) $prefixColumnsWithTable;
        return $this;
    }

    /**
     * Create join clause
     *
     * @param string|array $name
     * @param string $on
     * @param string|array $columns
     * @param  string $type one of the JOIN_* constants
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function join($name, $on, $columns = self::SQL_STAR, $type = self::JOIN_INNER)
    {
        $this->joins->join($name, $on, $columns, $type);

        return $this;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function where($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param string|array $group
     * @return self
     */
    public function group($group)
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
     * @param  Having|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @return self
     */
    public function having($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Having) {
            $this->having = $predicate;
        } else {
            $this->having->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param string|array $order
     * @return self
     */
    public function order($order)
    {
        if (is_string($order)) {
            if (strpos($order, ',') !== false) {
                $order = preg_split('#,\s+#', $order);
            } else {
                $order = (array) $order;
            }
        } elseif (!is_array($order)) {
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

    /**
     * @param int $limit
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function limit($limit)
    {
        if (!is_numeric($limit)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects parameter to be numeric, "%s" given',
                __METHOD__,
                (is_object($limit) ? get_class($limit) : gettype($limit))
            ));
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function offset($offset)
    {
        if (!is_numeric($offset)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects parameter to be numeric, "%s" given',
                __METHOD__,
                (is_object($offset) ? get_class($offset) : gettype($offset))
            ));
        }

        $this->offset = $offset;
        return $this;
    }

    /**
     * @param SelectableInterface $select
     * @param string $type
     * @param string $modifier
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function combine(SelectableInterface $select, $type = self::COMBINE_UNION, $modifier = '')
    {
        if ($this->combine !== []) {
            throw new Exception\InvalidArgumentException('This Select object is already combined and cannot be combined with multiple Selects objects');
        }
        $this->combine = [
            'select' => $select,
            'type' => $type,
            'modifier' => $modifier
        ];
        return $this;
    }


    public function __unset($name)
    {
        switch ($name) {
            case 'table':
                if ($this->tableReadOnly) {
                    throw new Exception\InvalidArgumentException(
                        'Since this object was created with a table and/or schema in the constructor, it is read only.'
                    );
                }
                $this->table = null;
                break;
            case 'quantifier':
                $this->quantifier = null;
                break;
            case 'columns':
                $this->columns = [self::SQL_STAR];
                break;
            case 'joins':
                $this->joins = new Join;
                break;
            case 'where':
                $this->where = new Where;
                break;
            case 'group':
                $this->group = null;
                break;
            case 'having':
                $this->having = new Having;
                break;
            case 'limit':
                $this->limit = null;
                break;
            case 'offset':
                $this->offset = null;
                break;
            case 'order':
                $this->order = [];
                break;
            case 'prefixColumnsWithTable' :
                $this->prefixColumnsWithTable = true;
                break;
            case 'combine':
                $this->combine = [];
                break;
            default :
                throw new Exception\InvalidArgumentException(
                    'Not a valid property "' . $name . '" for this object'
                );
        }
        return $this;
    }

    /**
     * Returns whether the table is read only or not.
     *
     * @return bool
     */
    public function isTableReadOnly()
    {
        return $this->tableReadOnly;
    }

    /**
     * __clone
     *
     * Resets the where object each time the Select is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->where  = clone $this->where;
        $this->joins  = clone $this->joins;
        $this->having = clone $this->having;
    }
}
