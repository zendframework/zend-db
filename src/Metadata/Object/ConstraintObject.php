<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata\Object;

class ConstraintObject
{
    /** @var string */
    protected $name = '';

    /** @var string */
    protected $tableName = '';

    /** @var string */
    protected $schemaName = '';

    /**
     * One of "PRIMARY KEY", "UNIQUE", "FOREIGN KEY", or "CHECK"
     *
     * @var string
     */
    protected $type = '';

    /** @var string[] */
    protected $columns = [];

    /** @var string */
    protected $referencedTableSchema = '';

    /** @var string */
    protected $referencedTableName = '';

    /** @var string[] */
    protected $referencedColumns = [];

    /** @var string */
    protected $matchOption = '';

    /** @var string */
    protected $updateRule = '';

    /** @var string */
    protected $deleteRule = '';

    /** @var string */
    protected $checkClause = '';

    public function __construct(string $name, string $tableName, string $schemaName = '')
    {
        $this->setName($name);
        $this->setTableName($tableName);
        $this->setSchemaName($schemaName);
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setSchemaName($schemaName) : void
    {
        $this->schemaName = $schemaName;
    }

    public function getSchemaName() : string
    {
        return $this->schemaName;
    }

    public function getTableName() : string
    {
        return $this->tableName;
    }

    public function setTableName($tableName) : self
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function setType($type) : void
    {
        $this->type = $type;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function hasColumns() : bool
    {
        return ! empty($this->columns);
    }

    public function getColumns() : array
    {
        return $this->columns;
    }

    public function setColumns(array $columns) : self
    {
        $this->columns = $columns;
        return $this;
    }

    public function getReferencedTableSchema() : string
    {
        return $this->referencedTableSchema;
    }

    public function setReferencedTableSchema($referencedTableSchema) : self
    {
        $this->referencedTableSchema = $referencedTableSchema;
        return $this;
    }

    public function getReferencedTableName() : string
    {
        return $this->referencedTableName;
    }

    public function setReferencedTableName($referencedTableName) : self
    {
        $this->referencedTableName = $referencedTableName;
        return $this;
    }

    public function getReferencedColumns() : array
    {
        return $this->referencedColumns;
    }

    public function setReferencedColumns(array $referencedColumns) : self
    {
        $this->referencedColumns = $referencedColumns;
        return $this;
    }

    public function getMatchOption() : string
    {
        return $this->matchOption;
    }

    public function setMatchOption($matchOption) : self
    {
        $this->matchOption = $matchOption;
        return $this;
    }

    public function getUpdateRule() : string
    {
        return $this->updateRule;
    }

    public function setUpdateRule($updateRule) : self
    {
        $this->updateRule = $updateRule;
        return $this;
    }

    public function getDeleteRule() : string
    {
        return $this->deleteRule;
    }

    public function setDeleteRule($deleteRule) : self
    {
        $this->deleteRule = $deleteRule;
        return $this;
    }

    public function getCheckClause() : string
    {
        return $this->checkClause;
    }

    public function setCheckClause($checkClause) : self
    {
        $this->checkClause = $checkClause;
        return $this;
    }

    public function isPrimaryKey() : bool
    {
        return ('PRIMARY KEY' === $this->type);
    }

    public function isUnique() : bool
    {
        return ('UNIQUE' === $this->type);
    }

    public function isForeignKey() : bool
    {
        return ('FOREIGN KEY' === $this->type);
    }

    public function isCheck() : bool
    {
        return ('CHECK' === $this->type);
    }
}
