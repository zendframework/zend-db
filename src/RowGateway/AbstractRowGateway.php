<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\RowGateway;

use ArrayAccess;
use Countable;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;

abstract class AbstractRowGateway implements ArrayAccess, Countable, RowGatewayInterface
{
    /** @var bool */
    protected $isInitialized = false;

    /** @var string|TableIdentifier */
    protected $table;

    /** @var array */
    protected $primaryKeyColumn = [];

    /** @var array */
    protected $primaryKeyData = [];

    /** @var array */
    protected $data = [];

    /** @var Sql */
    protected $sql;

    /** @var Feature\FeatureSet */
    protected $featureSet;

    /**
     * initialize()
     */
    public function initialize()
    {
        if ($this->isInitialized) {
            return;
        }

        if (! $this->featureSet instanceof Feature\FeatureSet) {
            $this->featureSet = new Feature\FeatureSet;
        }

        $this->featureSet->setRowGateway($this);
        $this->featureSet->apply('preInitialize', []);

        if (! is_string($this->table) && ! $this->table instanceof TableIdentifier) {
            throw new Exception\RuntimeException('This row object does not have a valid table set.');
        }

        if (is_string($this->primaryKeyColumn)) {
            $this->primaryKeyColumn = (array) $this->primaryKeyColumn;
        }

        if (count($this->primaryKeyColumn) === 0) {
            throw new Exception\RuntimeException('This row object does not have a primary key column set.');
        }

        if (! $this->sql instanceof Sql) {
            throw new Exception\RuntimeException('This row object does not have a Sql object set.');
        }

        $this->featureSet->apply('postInitialize', []);

        $this->isInitialized = true;
    }

    public function populate(array $rowData, bool $rowExistsInDatabase = false) : self
    {
        $this->initialize();

        $this->data = $rowData;
        if ($rowExistsInDatabase === true) {
            $this->processPrimaryKeyData();
        } else {
            $this->primaryKeyData = [];
        }

        return $this;
    }

    public function exchangeArray(array $array) : self
    {
        return $this->populate($array, true);
    }

    public function save() : int
    {
        $this->initialize();

        if ($this->rowExistsInDatabase()) {
            // UPDATE

            $data = $this->data;
            $where = [];
            $isPkModified = false;

            // primary key is always an array even if its a single column
            foreach ($this->primaryKeyColumn as $pkColumn) {
                $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
                if ($data[$pkColumn] == $this->primaryKeyData[$pkColumn]) {
                    unset($data[$pkColumn]);
                } else {
                    $isPkModified = true;
                }
            }

            $statement = $this->sql->prepareStatementForSqlObject($this->sql->update()->set($data)->where($where));
            $result = $statement->execute();
            $rowsAffected = $result->getAffectedRows();
            unset($statement, $result); // cleanup

            // If one or more primary keys are modified, we update the where clause
            if ($isPkModified) {
                foreach ($this->primaryKeyColumn as $pkColumn) {
                    if ($data[$pkColumn] != $this->primaryKeyData[$pkColumn]) {
                        $where[$pkColumn] = $data[$pkColumn];
                    }
                }
            }
        } else {
            // INSERT
            $insert = $this->sql->insert();
            $insert->values($this->data);

            $statement = $this->sql->prepareStatementForSqlObject($insert);

            $result = $statement->execute();
            if (($primaryKeyValue = $result->getGeneratedValue()) && count($this->primaryKeyColumn) === 1) {
                $this->primaryKeyData = [$this->primaryKeyColumn[0] => $primaryKeyValue];
            } else {
                // make primary key data available so that $where can be complete
                $this->processPrimaryKeyData();
            }
            $rowsAffected = $result->getAffectedRows();
            unset($statement, $result); // cleanup

            $where = [];
            // primary key is always an array even if its a single column
            foreach ($this->primaryKeyColumn as $pkColumn) {
                $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
            }
        }

        // refresh data
        $statement = $this->sql->prepareStatementForSqlObject($this->sql->select()->where($where));
        $result = $statement->execute();
        $rowData = $result->current();
        unset($statement, $result); // cleanup

        // make sure data and original data are in sync after save
        $this->populate($rowData, true);

        // return rows affected
        return $rowsAffected;
    }

    public function delete() : int
    {
        $this->initialize();

        $where = [];
        // primary key is always an array even if its a single column
        foreach ($this->primaryKeyColumn as $pkColumn) {
            $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
        }

        // @todo determine if we need to do a select to ensure 1 row will be affected

        $statement = $this->sql->prepareStatementForSqlObject($this->sql->delete()->where($where));
        $result = $statement->execute();

        $affectedRows = $result->getAffectedRows();
        if ($affectedRows === 1) {
            // detach from database
            $this->primaryKeyData = [];
        }

        return $affectedRows;
    }

    /**
     * Offset Exists
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset get
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * Offset set
     *
     * @param  string $offset
     * @param  mixed $value
     * @return self Provides a fluent interface
     */
    public function offsetSet($offset, $value) : self
    {
        $this->data[$offset] = $value;
        return $this;
    }

    /**
     * Offset unset
     *
     * @param  string $offset
     * @return self Provides a fluent interface
     */
    public function offsetUnset($offset) : self
    {
        $this->data[$offset] = null;
        return $this;
    }

    public function count() : int
    {
        return count($this->data);
    }

    public function toArray() : array
    {
        return $this->data;
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        throw new Exception\InvalidArgumentException('Not a valid column in this row: ' . $name);
    }

    public function __set(string $name, $value) : void
    {
        $this->offsetSet($name, $value);
    }

    public function __isset(string $name) : bool
    {
        return $this->offsetExists($name);
    }

    public function __unset(string $name) : void
    {
        $this->offsetUnset($name);
    }

    public function rowExistsInDatabase() : bool
    {
        return count($this->primaryKeyData) > 0;
    }

    protected function processPrimaryKeyData() : void
    {
        $this->primaryKeyData = [];
        foreach ($this->primaryKeyColumn as $column) {
            if (! isset($this->data[$column])) {
                throw new Exception\RuntimeException(
                    'While processing primary key data, a known key ' . $column . ' was not found in the data array'
                );
            }
            $this->primaryKeyData[$column] = $this->data[$column];
        }
    }
}
