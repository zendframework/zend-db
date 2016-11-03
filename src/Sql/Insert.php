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
 * @property array $columns
 * @property array|SelectableInterface $values
 */
class Insert extends AbstractSqlObject implements PreparableSqlObjectInterface
{
    const VALUES_MERGE = 'merge';
    const VALUES_SET   = 'set';

    /**
     * @var TableSource
     */
    protected $table            = null;
    protected $columns          = [];
    protected $values           = [];

    protected $__getProperties = [
        'table',
        'columns',
        'values',
    ];

    /**
     * Constructor
     *
     * @param  null|string|array|TableIdentifier|TableSource $table
     */
    public function __construct($table = null)
    {
        parent::__construct();
        $this->into($table);
    }

    /**
     * Create INTO clause
     *
     * @param  string|array|TableIdentifier|TableSource $table
     * @return self
     */
    public function into($table)
    {
        $this->table = TableSource::factory($table);
        return $this;
    }

    /**
     * Specify columns
     *
     * @param  array $columns
     * @return self
     */
    public function columns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Specify values to insert
     *
     * @param  array|SelectableInterface $values
     * @param  string $flag one of VALUES_MERGE or VALUES_SET; defaults to VALUES_SET
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function values($values, $flag = self::VALUES_SET)
    {
        if ($values instanceof SelectableInterface) {
            if ($flag == self::VALUES_MERGE) {
                throw new Exception\InvalidArgumentException(
                    'A Zend\Db\Sql\SelectableInterface instance cannot be provided with the merge flag'
                );
            }
            $this->values = $values;
            return $this;
        }

        if (!is_array($values)) {
            throw new Exception\InvalidArgumentException(
                'values() expects an array of values or Zend\Db\Sql\SelectableInterface instance'
            );
        }
        if ($this->values instanceof SelectableInterface && $flag == self::VALUES_MERGE) {
            throw new Exception\InvalidArgumentException(
                'An array of values cannot be provided with the merge flag when a Zend\Db\Sql\SelectableInterface instance already exists as the value source'
            );
        }

        $columns = null;
        if (!is_numeric(key($values))) {
            $columns = array_keys($values);
            $values = array_values($values);
        }

        if ($flag == self::VALUES_SET) {
            $this->values = $values;
            if ($columns) {
                $this->columns = $columns;
            }
            return $this;
        }

        if (!$columns) {
            $this->values = array_merge($this->values, $values);
            return $this;
        }

        foreach ($columns as $i=>$column) {
            if (($k = array_search($column, $this->columns)) !== false) {
                $this->values[$k] = $values[$i];
                unset($values[$i], $columns[$i]);
            } else {
                $this->values[] = $values[$i];
                $this->columns[] = $column;
            }
        }

        return $this;
    }

    /**
     * Create INTO SELECT clause
     *
     * @param SelectableInterface $select
     * @return self
     */
    public function select(SelectableInterface $select)
    {
        return $this->values($select);
    }

    public function __clone()
    {
        $this->table = clone $this->table;
    }
}
