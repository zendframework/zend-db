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
 * @property TableSource $table
 * @property Where $where
 */
class Delete extends AbstractSqlObject implements PreparableSqlObjectInterface
{
    /**
     * @var TableSource
     */
    protected $table = '';

    /**
     * @var null|string|Where
     */
    protected $where = null;

    protected $__getProperties = [
        'table',
        'where',
    ];

    /**
     * Constructor
     *
     * @param  null|string|array|TableIdentifier|TableSource $table
     */
    public function __construct($table = null)
    {
        parent::__construct();
        $this->from($table);
        $this->where = new Where();
    }

    /**
     * Create from statement
     *
     * @param  string|array|TableIdentifier|TableSource $table
     * @return Delete
     */
    public function from($table)
    {
        $this->table = TableSource::factory($table);
        return $this;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @return Delete
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

    public function __clone()
    {
        $this->table = clone $this->table;
    }
}
