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
 */
class TableIdentifier
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var null|string
     */
    protected $schema;

    public static function factory($table, $schema = null)
    {
        if (null === $table) {
            return;
        }
        if ($table instanceof self) {
            return $table;
        }
        return new self($table, $schema);
    }
    /**
     * @param string      $table
     * @param null|string $schema
     */
    public function __construct($table, $schema = null)
    {
        if (is_array($table)) {
            $schema = isset($table[0]) ? $table[0] : $schema;
            $table  = isset($table[1]) ? $table[1] : null;
        }
        if (! (is_string($table) || is_callable([$table, '__toString']))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$table must be a valid table name, parameter of type %s given',
                is_object($table) ? get_class($table) : gettype($table)
            ));
        }

        $this->table = (string) $table;

        if ('' === $this->table) {
            throw new Exception\InvalidArgumentException('$table must be a valid table name, empty string given');
        }

        if (null === $schema) {
            $this->schema = null;
        } else {
            if (! (is_string($schema) || is_callable([$schema, '__toString']))) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '$schema must be a valid schema name, parameter of type %s given',
                    is_object($schema) ? get_class($schema) : gettype($schema)
                ));
            }

            $this->schema = (string) $schema;

            if ('' === $this->schema) {
                throw new Exception\InvalidArgumentException(
                    '$schema must be a valid schema name or null, empty string given'
                );
            }
        }
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return bool
     */
    public function hasSchema()
    {
        return ($this->schema !== null);
    }

    /**
     * @return null|string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    public function getTableAndSchema()
    {
        return [$this->table, $this->schema];
    }
}
