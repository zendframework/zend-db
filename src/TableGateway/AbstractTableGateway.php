<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Join;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\Feature\EventFeatureEventsInterface;

/**
 *
 * @property AdapterInterface $adapter
 * @property int $lastInsertValue
 * @property string $table
 */
abstract class AbstractTableGateway implements TableGatewayInterface
{
    /** @var bool */
    protected $isInitialized = false;

    /** @var AdapterInterface */
    protected $adapter = null;

    /** @var string|array|TableIdentifier */
    protected $table = null;

    /** @var array */
    protected $columns = [];

    /** @var Feature\FeatureSet */
    protected $featureSet = null;

    /** @var ResultSetInterface */
    protected $resultSetPrototype = null;

    /** @var Sql */
    protected $sql;

    /** @var int */
    protected $lastInsertValue = null;

    public function isInitialized() : bool
    {
        return $this->isInitialized;
    }

    public function initialize() : void
    {
        if ($this->isInitialized) {
            return;
        }

        if (! $this->featureSet instanceof Feature\FeatureSet) {
            $this->featureSet = new Feature\FeatureSet;
        }

        $this->featureSet->setTableGateway($this);
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_INITIALIZE, []);

        if (! $this->adapter instanceof AdapterInterface) {
            throw new Exception\RuntimeException('This table does not have an Adapter setup');
        }

        if (! is_string($this->table) && ! $this->table instanceof TableIdentifier && ! is_array($this->table)) {
            throw new Exception\RuntimeException('This table object does not have a valid table set.');
        }

        if (! $this->resultSetPrototype instanceof ResultSetInterface) {
            $this->resultSetPrototype = new ResultSet;
        }

        if (! $this->sql instanceof Sql) {
            $this->sql = new Sql($this->adapter, $this->table);
        }

        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_INITIALIZE, []);

        $this->isInitialized = true;
    }

    public function getTable() : string
    {
        return $this->table;
    }

    public function getAdapter() : AdapterInterface
    {
        return $this->adapter;
    }

    public function getColumns() : array
    {
        return $this->columns;
    }

    public function getFeatureSet() : Feature\FeatureSet
    {
        return $this->featureSet;
    }

    public function getResultSetPrototype() : ResultSetInterface
    {
        return $this->resultSetPrototype;
    }

    public function getSql() : Sql
    {
        return $this->sql;
    }

    /**
     * Select
     *
     * @param Where|\Closure|string|array $where
     * @return ResultSetInterface
     */
    public function select($where = null) : ResultSetInterface
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }

        $select = $this->sql->select();

        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }

        return $this->selectWith($select);
    }

    public function selectWith(Select $select) : ResultSetInterface
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        return $this->executeSelect($select);
    }

    protected function executeSelect(Select $select) : ResultSetInterface
    {
        $selectState = $select->getRawState();
        if ($selectState['table'] != $this->table
            && (is_array($selectState['table'])
                && end($selectState['table']) != $this->table)
        ) {
            throw new Exception\RuntimeException(
                'The table name of the provided Select object must match that of the table'
            );
        }

        if ($selectState['columns'] == [Select::SQL_STAR]
            && $this->columns !== []) {
            $select->columns($this->columns);
        }

        // apply preSelect features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_SELECT, [$select]);

        // prepare and execute
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        // build result set
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        // apply postSelect features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_SELECT, [$statement, $result, $resultSet]);

        return $resultSet;
    }

    /**
     * Insert
     *
     * @param  array $set
     * @return int
     */
    public function insert($set) : int
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        $insert = $this->sql->insert();
        $insert->values($set);
        return $this->executeInsert($insert);
    }

    public function insertWith(Insert $insert) : int
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        return $this->executeInsert($insert);
    }

    protected function executeInsert(Insert $insert) : int
    {
        // @todo add $columns support
        $insertState = $insert->getRawState();
        if ($insertState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Insert object must match that of the table'
            );
        }

        // apply preInsert features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_INSERT, [$insert]);

        // Most RDBMS solutions do not allow using table aliases in INSERTs
        // See https://github.com/zendframework/zf2/issues/7311
        $unaliasedTable = false;
        if (is_array($insertState['table'])) {
            $tableData      = array_values($insertState['table']);
            $unaliasedTable = array_shift($tableData);
            $insert->into($unaliasedTable);
        }

        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();
        $this->lastInsertValue = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();

        // apply postInsert features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_INSERT, [$statement, $result]);

        // Reset original table information in Insert instance, if necessary
        if ($unaliasedTable) {
            $insert->into($insertState['table']);
        }

        return $result->getAffectedRows();
    }

    /**
     * Update
     *
     * @param  array $set
     * @param  string|array|\Closure $where
     * @param  null|array $joins
     * @return int
     */
    public function update($set, $where = null, array $joins = null) : int
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        $sql = $this->sql;
        $update = $sql->update();
        $update->set($set);
        if ($where !== null) {
            $update->where($where);
        }

        if ($joins) {
            foreach ($joins as $join) {
                $type = isset($join['type']) ? $join['type'] : Join::JOIN_INNER;
                $update->join($join['name'], $join['on'], $type);
            }
        }

        return $this->executeUpdate($update);
    }

    public function updateWith(Update $update) : int
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        return $this->executeUpdate($update);
    }

    protected function executeUpdate(Update $update) : int
    {
        // @todo add $columns support
        $updateState = $update->getRawState();
        if ($updateState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Update object must match that of the table'
            );
        }

        // apply preUpdate features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_UPDATE, [$update]);

        $unaliasedTable = false;
        if (is_array($updateState['table'])) {
            $tableData      = array_values($updateState['table']);
            $unaliasedTable = array_shift($tableData);
            $update->table($unaliasedTable);
        }

        $statement = $this->sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        // apply postUpdate features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_UPDATE, [$statement, $result]);

        // Reset original table information in Update instance, if necessary
        if ($unaliasedTable) {
            $update->table($updateState['table']);
        }

        return $result->getAffectedRows();
    }

    /**
     * Delete
     *
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    public function delete($where) : int
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        $delete = $this->sql->delete();
        if ($where instanceof \Closure) {
            $where($delete);
        } else {
            $delete->where($where);
        }
        return $this->executeDelete($delete);
    }

    public function deleteWith(Delete $delete) : int
    {
        $this->initialize();
        return $this->executeDelete($delete);
    }

    protected function executeDelete(Delete $delete) : int
    {
        // @todo add $columns support
        $deleteState = $delete->getRawState();
        if ($deleteState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Delete object must match that of the table'
            );
        }

        // pre delete update
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_DELETE, [$delete]);

        $statement = $this->sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        // apply postDelete features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_DELETE, [$statement, $result]);

        return $result->getAffectedRows();
    }

    public function getLastInsertValue() : int
    {
        return $this->lastInsertValue;
    }

    public function __get(string $property)
    {
        switch (strtolower($property)) {
            case 'lastinsertvalue':
                return $this->lastInsertValue;
            case 'adapter':
                return $this->adapter;
            case 'table':
                return $this->table;
        }
        if ($this->featureSet->canCallMagicGet($property)) {
            return $this->featureSet->callMagicGet($property);
        }
        throw new Exception\InvalidArgumentException('Invalid magic property access in ' . __CLASS__ . '::__get()');
    }

    public function __set(string $property, $value)
    {
        if ($this->featureSet->canCallMagicSet($property)) {
            return $this->featureSet->callMagicSet($property, $value);
        }
        throw new Exception\InvalidArgumentException('Invalid magic property access in ' . __CLASS__ . '::__set()');
    }

    public function __call(string $method, array $arguments)
    {
        if ($this->featureSet->canCallMagicCall($method)) {
            return $this->featureSet->callMagicCall($method, $arguments);
        }
        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid method (%s) called, caught by %s::__call()',
            $method,
            __CLASS__
        ));
    }

    public function __clone()
    {
        $this->resultSetPrototype = isset($this->resultSetPrototype) ? clone $this->resultSetPrototype : null;
        $this->sql = clone $this->sql;
        if (is_object($this->table)) {
            $this->table = clone $this->table;
        } elseif (is_array($this->table)
            && count($this->table) == 1
            && is_object(reset($this->table))
        ) {
            foreach ($this->table as $alias => &$tableObject) {
                $tableObject = clone $tableObject;
            }
        }
    }
}
