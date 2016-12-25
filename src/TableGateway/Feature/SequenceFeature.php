<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Insert;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Sql\TableIdentifier;

class SequenceFeature extends AbstractFeature
{
    /**
     * @var string
     */
    protected $primaryKeyField;

    /**
     * @var string
     */
    protected $sequenceName;

    /**
     * @var int
     */
    protected $sequenceValue;


    /**
     * @param string $primaryKeyField
     * @param string|array|null $sequenceName
     */
    public function __construct($primaryKeyField, $sequenceName = null)
    {
        $this->primaryKeyField = $primaryKeyField;
        $this->sequenceName    = $sequenceName;
    }

    /**
     * @return string
     */
    public function getSequenceName() {
        if ($this->sequenceName !== null) {
            return $this->sequenceName;
        }

        //@TODO move to PostgreSQL specific class (possibly decorator)
        /** @var Adapter $adapter */
        $adapter = $this->tableGateway->getAdapter();
        $platform = $adapter->getPlatform();
        $tableIdentifier = $this->tableGateway->getTable();
        // need to preserve table name in case have to query postgres metadata
        // (case for large resultant identifier names)
        $tableName = '';

        $sequenceSuffix = '_' . $this->primaryKeyField . '_seq';
        // To find whether exceed identifier length, need to keep track of combination of
        // table name ane suffix but not including schema name.
        // Since schema has to be appended in the end,
        $sequenceObjectName = '';

        if(is_string($tableIdentifier)) {
            $tableName = $tableIdentifier;

            $sequenceObjectName = $this->sequenceName = $tableIdentifier . $sequenceSuffix;
        } elseif(is_array($tableIdentifier)) {
            // assuming key 0 is schema name
            $tableName = $tableIdentifier[1];

            $this->sequenceName = $tableIdentifier;
            $this->sequenceName[1] = $tableName.$sequenceSuffix;
            $sequenceObjectName = $this->sequenceName[1];
        } elseif($tableIdentifier instanceof TableIdentifier) {
            $tableName = $tableIdentifier->getTable();
            $sequenceObjectName = $tableName.$sequenceSuffix;
            $this->sequenceName = $tableIdentifier->hasSchema() ? [$tableIdentifier->getSchema(), $sequenceObjectName] : $sequenceObjectName;
        }

        if(strlen($sequenceObjectName) < 64 ) {
            $this->sequenceName = $platform->quoteIdentifierChain($this->sequenceName);
            return  $this->sequenceName;
        }

        $statement = $adapter->createStatement();
        $statement->prepare('SELECT pg_get_serial_sequence(:table, :column)');
        $result = $statement->execute(['table' => $tableIdentifier, 'column' => $this->primaryKeyField]);
        $this->sequenceName = $result->current()['pg_get_serial_sequence'];

        // there could be a benefit porting this algorithm here instead of extra query call
        // https://github.com/postgres/postgres/blob/f0e44751d7175fa3394da2c8f85e3ceb3cdbfe63/src/backend/commands/indexcmds.c#L1485

        return $this->sequenceName;
    }

    /**
     * @param Insert $insert
     * @return Insert
     */
    public function preInsert(Insert $insert)
    {
        $columns = $insert->getRawState('columns');
        $values = $insert->getRawState('values');
        $key = array_search($this->primaryKeyField, $columns);
        if ($key !== false) {
            $this->sequenceValue = $values[$key];
            return $insert;
        }

        $this->sequenceValue = $this->nextSequenceId();
        if ($this->sequenceValue === null) {
            return $insert;
        }

        $insert->values([$this->primaryKeyField => $this->sequenceValue],  Insert::VALUES_MERGE);
        return $insert;
    }

    /**
     * @param StatementInterface $statement
     * @param ResultInterface $result
     */
    public function postInsert(StatementInterface $statement, ResultInterface $result)
    {
        if ($this->sequenceValue !== null) {
            $this->tableGateway->lastInsertValue = $this->sequenceValue;
        }
    }

    /**
     * Generate a new value from the specified sequence in the database, and return it.
     * @param $columnName string Column name which this sequence instance is expected to manage.
     * If expectation does not match, ignore the call.
     * @return int
     */
    public function nextSequenceId($columnName = null)
    {
        if ($columnName !== null && strcmp($columnName, $this->primaryKeyField) !== 0) {
            return;
        }

        /** @var Adapter $adapter */
        $adapter = $this->tableGateway->adapter;
        $platform = $adapter->getPlatform();
        $platformName = $platform->getName();

        switch ($platformName) {
            case 'Oracle':
                $sql = 'SELECT ' . $platform->quoteIdentifier($this->sequenceName) . '.NEXTVAL as "nextval" FROM dual';
                $param = [];
                break;
            case 'PostgreSQL':
                $sql = 'SELECT NEXTVAL( :sequence_name )';
                $param = ['sequence_name' => $this->getSequenceName()];
                break;
            default :
                return;
        }

        $statement = $adapter->createStatement();
        $statement->prepare($sql);
        $result = $statement->execute($param);
        $sequence = $result->current();
        unset($statement, $result);
        return $sequence['nextval'];
    }

    /**
     * Return the most recent value from the specified sequence in the database.
     * @param $columnName string Column name which this sequence instance is expected to manage.
     * If expectation does not match, ignore the call.
     * @return int
     */
    public function lastSequenceId($columnName = null)
    {
        if ($columnName !== null && strcmp($columnName, $this->primaryKeyField) !== 0) {
            return;
        }

        /** @var Adapter $adapter */
        $adapter = $this->tableGateway->adapter;
        $platform = $adapter->getPlatform();
        $platformName = $platform->getName();

        switch ($platformName) {
            case 'Oracle':
                $sql = 'SELECT ' . $platform->quoteIdentifier($this->sequenceName) . '.CURRVAL as "currval" FROM dual';
                $param = [];
                break;
            case 'PostgreSQL':
                $sql = 'SELECT CURRVAL( :sequence_name )';
                $param = ['sequence_name' => $this->getSequenceName()];
                break;
            //@TODO add SQLServer2016
            default :
                return;
        }

        $statement = $adapter->createStatement();
        $statement->prepare($sql);
        $result = $statement->execute($param);
        $sequence = $result->current();
        unset($statement, $result);
        return $sequence['currval'];
    }
}
