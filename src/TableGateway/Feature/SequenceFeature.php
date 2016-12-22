<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway\Feature;

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

        $platform = $this->tableGateway->getAdapter()->getPlatform();
        $table = $this->tableGateway->getTable();

        $sequenceSuffix = '_' . $this->primaryKeyField . '_seq';

        if(is_string($table)) {
            $this->sequenceName = $table . $sequenceSuffix;
        } elseif(is_array($table)) {
            // assuming key 0 is schema name
            $table[1] .= $sequenceSuffix;
            $this->sequenceName = $table;
        } elseif($table instanceof TableIdentifier) {
            $this->sequenceName = $table->hasSchema() ? [$table->getSchema(), $table->getTable().$sequenceSuffix] : $table->getTable().$sequenceSuffix;
        }

        $this->sequenceName = $platform->quoteIdentifierChain($this->sequenceName);

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

        $platform = $this->tableGateway->adapter->getPlatform();
        $platformName = $platform->getName();

        switch ($platformName) {
            case 'Oracle':
                $sql = 'SELECT ' . $platform->quoteIdentifier($this->sequenceName) . '.NEXTVAL as "nextval" FROM dual';
                break;
            case 'PostgreSQL':
                $sql = 'SELECT NEXTVAL(\'"' . $this->sequenceName . '"\')';
                break;
            default :
                return;
        }

        $statement = $this->tableGateway->adapter->createStatement();
        $statement->prepare($sql);
        $result = $statement->execute();
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

        $platform = $this->tableGateway->adapter->getPlatform();
        $platformName = $platform->getName();

        switch ($platformName) {
            case 'Oracle':
                $sql = 'SELECT ' . $platform->quoteIdentifier($this->sequenceName) . '.CURRVAL as "currval" FROM dual';
                break;
            case 'PostgreSQL':
                $sql = 'SELECT CURRVAL(\'' . $this->sequenceName . '\')';
                break;
            default :
                return;
        }

        $statement = $this->tableGateway->adapter->createStatement();
        $statement->prepare($sql);
        $result = $statement->execute();
        $sequence = $result->current();
        unset($statement, $result);
        return $sequence['currval'];
    }
}
