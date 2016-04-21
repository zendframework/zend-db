<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Stdlib\PriorityList;

/**
 * @property TableSource $table
 * @property PriorityList $set
 * @property Where $where
 */
class Update extends AbstractSqlObject implements PreparableSqlObjectInterface
{
    const VALUES_MERGE = 'merge';
    const VALUES_SET   = 'set';

    /**
     * @var TableSource
     */
    protected $table = '';

    /**
     * @var PriorityList
     */
    protected $set;

    /**
     * @var string|Where
     */
    protected $where = null;

    protected $__getProperties = [
        'table',
        'set',
        'where',
        'joins',
    ];

    /**
     * @var null|Joins
     */
    protected $joins = null;

    /**
     * Constructor
     *
     * @param  null|string|array|TableIdentifier|TableSource $table
     */
    public function __construct($table = null)
    {
        parent::__construct();
        $this->table($table);
        $this->where = new Where();
        $this->joins = new Joins();
        $this->set = new PriorityList();
        $this->set->isLIFO(false);
    }

    /**
     * Specify table for statement
     *
     * @param  string|array|TableIdentifier|TableSource $table
     * @return self
     */
    public function table($table)
    {
        $this->table = TableSource::factory($table);
        return $this;
    }

    /**
     * Set key/value pairs to update
     *
     * @param  array $values Associative array of key values
     * @param  string $flag One of the VALUES_* constants
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function set(array $values, $flag = self::VALUES_SET)
    {
        if ($values == null) {
            throw new Exception\InvalidArgumentException('set() expects an array of values');
        }

        if ($flag == self::VALUES_SET) {
            $this->set->clear();
        }
        $priority = is_numeric($flag) ? $flag : 0;
        foreach ($values as $k => $v) {
            if (!is_string($k)) {
                throw new Exception\InvalidArgumentException('set() expects a string for the value key');
            }
            $this->set->insert($k, $v, $priority);
        }
        return $this;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
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
     * Create join clause
     *
     * @param  string|array $name
     * @param  string $on
     * @param  string $type one of the JOIN_* constants
     * @throws Exception\InvalidArgumentException
     * @return Update
     */
    public function join($name, $on, $type = Joins::JOIN_INNER)
    {
        $this->joins->join($name, $on, [], $type);

        return $this;
    }


    /**
     * __clone
     *
     * Resets the where object each time the Update is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->table = clone $this->table;
        $this->joins = clone $this->joins;
        $this->where = clone $this->where;
        $this->set = clone $this->set;
    }
}
