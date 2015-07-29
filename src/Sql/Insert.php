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
 * @property null|Select $select
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

    /**
     * @var array|Select
     */
    protected $select           = null;

    protected $__getProperties = [
        'table',
        'columns',
        'select',
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
        $this->columns = array_flip($columns);
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
            $this->select = $values;
            return $this;
        }

        if (!is_array($values)) {
            throw new Exception\InvalidArgumentException(
                'values() expects an array of values or Zend\Db\Sql\SelectableInterface instance'
            );
        }

        if ($this->select && $flag == self::VALUES_MERGE) {
            throw new Exception\InvalidArgumentException(
                'An array of values cannot be provided with the merge flag when a Zend\Db\Sql\SelectableInterface instance already exists as the value source'
            );
        }

        if ($flag == self::VALUES_SET) {
            $this->columns = $this->isAssocativeArray($values)
                ? $values
                : array_combine(array_keys($this->columns), array_values($values));
        } else {
            foreach ($values as $column => $value) {
                $this->columns[$column] = $value;
            }
        }
        return $this;
    }


    /**
     * Simple test for an associative array
     *
     * @link http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     * @param array $array
     * @return bool
     */
    private function isAssocativeArray(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
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

    public function __get($name)
    {
        if ($name == 'columns') {
            return array_keys($this->columns);
        }
        if ($name == 'values') {
            return array_values($this->columns);
        }
        return parent::__get($name);
    }

    public function __clone()
    {
        $this->table = clone $this->table;
    }
}
