<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;

class Insert extends AbstractPreparableSql
{
    /**
     * Constants
     *
     * @const
     */
    public const SPECIFICATION_INSERT = 'insert';
    public const SPECIFICATION_SELECT = 'select';
    public const VALUES_MERGE = 'merge';
    public const VALUES_SET   = 'set';
    /**#@-*/

    /** @var array Specification array */
    protected $specifications = [
        self::SPECIFICATION_INSERT => 'INSERT INTO %1$s (%2$s) VALUES (%3$s)',
        self::SPECIFICATION_SELECT => 'INSERT INTO %1$s %2$s %3$s',
    ];

    /** @var string|TableIdentifier */
    protected $table;
    protected $columns          = [];

    /** @var array|Select */
    protected $select;

    /**
     * Constructor
     *
     * @param  null|string|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->into($table);
        }
    }

    /**
     * Create INTO clause
     *
     * @param  string|TableIdentifier $table
     * @return self Provides a fluent interface
     */
    public function into($table) : self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Specify columns
     *
     * @param  array $columns
     * @return self Provides a fluent interface
     */
    public function columns(array $columns) : self
    {
        $this->columns = array_flip($columns);
        return $this;
    }

    /**
     * Specify values to insert
     *
     * @param  array|Select $values
     * @param  string $flag one of VALUES_MERGE or VALUES_SET; defaults to VALUES_SET
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function values($values, string $flag = self::VALUES_SET) : self
    {
        if ($values instanceof Select) {
            if ($flag === self::VALUES_MERGE) {
                throw new Exception\InvalidArgumentException(
                    'A Zend\Db\Sql\Select instance cannot be provided with the merge flag'
                );
            }
            $this->select = $values;
            return $this;
        }

        if (! is_array($values)) {
            throw new Exception\InvalidArgumentException(
                'values() expects an array of values or Zend\Db\Sql\Select instance'
            );
        }

        if ($this->select && $flag === self::VALUES_MERGE) {
            throw new Exception\InvalidArgumentException(
                'An array of values cannot be provided with the merge flag when a Zend\Db\Sql\Select instance already '
                . 'exists as the value source'
            );
        }

        if ($flag === self::VALUES_SET) {
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
    private function isAssocativeArray(array $array) : bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public function select(Select $select) : self
    {
        return $this->values($select);
    }

    public function getRawState(?string $key = null)
    {
        $rawState = [
            'table' => $this->table,
            'columns' => array_keys($this->columns),
            'values' => array_values($this->columns)
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    protected function processInsert(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if ($this->select) {
            return;
        }
        if (! $this->columns) {
            throw new Exception\InvalidArgumentException('values or select should be present');
        }

        $columns = [];
        $values  = [];
        $i       = 0;

        foreach ($this->columns as $column => $value) {
            $columns[] = $platform->quoteIdentifier($column);
            if (is_scalar($value) && $parameterContainer) {
                // use incremental value instead of column name for PDO
                // @see https://github.com/zendframework/zend-db/issues/35
                if ($driver instanceof Pdo) {
                    $column = 'c_' . $i++;
                }
                $values[] = $driver->formatParameterName($column);
                $parameterContainer->offsetSet($column, $value);
            } else {
                $values[] = $this->resolveColumnValue(
                    $value,
                    $platform,
                    $driver,
                    $parameterContainer
                );
            }
        }
        return sprintf(
            $this->specifications[static::SPECIFICATION_INSERT],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer),
            implode(', ', $columns),
            implode(', ', $values)
        );
    }

    protected function processSelect(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if (! $this->select) {
            return;
        }
        $selectSql = $this->processSubSelect($this->select, $platform, $driver, $parameterContainer);

        $columns = array_map([$platform, 'quoteIdentifier'], array_keys($this->columns));
        $columns = implode(', ', $columns);

        return sprintf(
            $this->specifications[static::SPECIFICATION_SELECT],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer),
            $columns ? "($columns)" : "",
            $selectSql
        );
    }

    public function __set(string $name, $value)
    {
        $this->columns[$name] = $value;
        return $this;
    }

    public function __unset(string $name)
    {
        if (! array_key_exists($name, $this->columns)) {
            throw new Exception\InvalidArgumentException(
                'The key ' . $name . ' was not found in this objects column list'
            );
        }

        unset($this->columns[$name]);
    }

    public function __isset(string $name)
    {
        return array_key_exists($name, $this->columns);
    }

    public function __get(string $name)
    {
        if (! array_key_exists($name, $this->columns)) {
            throw new Exception\InvalidArgumentException(
                'The key ' . $name . ' was not found in this objects column list'
            );
        }
        return $this->columns[$name];
    }
}
