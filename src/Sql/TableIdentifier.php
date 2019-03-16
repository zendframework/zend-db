<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

class TableIdentifier
{
    /** @var string */
    protected $table;

    /** @var null|string */
    protected $schema;

    public function __construct(string $table, ?string $schema = null)
    {
        if (! (is_string($table) || is_callable([$table, '__toString']))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$table must be a valid table name, parameter of type %s given',
                is_object($table) ? get_class($table) : gettype($table)
            ));
        }

        $this->table = $table;

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

            $this->schema = $schema;

            if ('' === $this->schema) {
                throw new Exception\InvalidArgumentException(
                    '$schema must be a valid schema name or null, empty string given'
                );
            }
        }
    }

    /**
     * @param string $table
     * @deprecated please use the constructor and build a new {@see TableIdentifier} instead
     */
    public function setTable(string $table) : void
    {
        $this->table = $table;
    }

    public function getTable() : string
    {
        return $this->table;
    }

    public function hasSchema() : bool
    {
        return ($this->schema !== null);
    }

    /**
     * @param null|string $schema
     * @deprecated please use the constructor and build a new {@see TableIdentifier} instead
     */
    public function setSchema(?string $schema) : void
    {
        $this->schema = $schema;
    }

    /**
     * @return null|string
     */
    public function getSchema() : ?string
    {
        return $this->schema;
    }

    public function getTableAndSchema() : array
    {
        return [$this->table, $this->schema];
    }
}
