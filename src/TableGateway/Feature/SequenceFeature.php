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
     * @var string
     */
    protected $schemaName = null;

    /**
     * @var int
     */
    protected $sequenceValue;


    /**
     * @param string $primaryKeyField
     * @param string $sequenceName
     */
    public function __construct($primaryKeyField, $sequenceName)
    {
        $this->primaryKeyField = $primaryKeyField;
        $this->sequenceName    = $sequenceName;

        if ($sequenceName instanceof TableIdentifier) {
            $this->sequenceName = $sequenceName->getTable();
            $this->schemaName = $sequenceName->getSchema();
        }
    }

    /**
     * @param Insert $insert
     * @return Insert
     */
    public function preInsert(Insert $insert)
    {
        $this->tableGateway->lastInsertValue = $this->lastSequenceId();

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

        $insert->values([$this->primaryKeyField => $this->sequenceValue], Insert::VALUES_MERGE);
        return $insert;
    }

    /**
     * @param StatementInterface $statement
     * @param ResultInterface $result
     */
    public function postInsert(StatementInterface $statement, ResultInterface $result)
    {
        $this->tableGateway->lastInsertValue = $this->sequenceValue;
    }

    /**
     * Generate a new value from the specified sequence in the database, and return it.
     * @return int
     */
    public function nextSequenceId()
    {
        $platform = $this->tableGateway->adapter->getPlatform();
        $platformName = $platform->getName();

        switch ($platformName) {
            case 'Oracle':
                $sql = 'SELECT ' . $platform->quoteIdentifier($this->sequenceName) . '.NEXTVAL as "nextval" FROM dual';
                break;
            case 'PostgreSQL':
                $sql = 'SELECT NEXTVAL(\'"' . $this->sequenceName . '"\')';
                if ($this->schemaName !== null) {
                    $sql = 'SELECT NEXTVAL(\'"' . $this->schemaName . '"."' . $this->sequenceName . '"\')';
                }
                break;
            default:
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
     * @return int
     */
    public function lastSequenceId()
    {
        $platform = $this->tableGateway->adapter->getPlatform();
        $platformName = $platform->getName();

        switch ($platformName) {
            case 'Oracle':
                $sql = 'SELECT ' . $platform->quoteIdentifier($this->sequenceName) . '.CURRVAL as "currval" FROM dual';
                break;
            case 'PostgreSQL':
                $sql = 'SELECT CURRVAL(\'' . $this->sequenceName . '\')';
                if ($this->schemaName !== null) {
                    $sql = 'SELECT CURRVAL(\'"' . $this->schemaName . '"."' . $this->sequenceName . '"\')';
                }
                break;
            default:
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
