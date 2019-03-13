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
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @var string
     */
    protected $schemaName = '';

    /**
     * One of "PRIMARY KEY", "UNIQUE", "FOREIGN KEY", or "CHECK"
     *
     * @var string
     */
    protected $type = '';

    /**
     * @var string[]
     */
    protected $columns = [];

    /**
     * @var string
     */
    protected $referencedTableSchema = '';

    /**
     * @var string
     */
    protected $referencedTableName = '';

    /**
     * @var string[]
     */
    protected $referencedColumns = [];

    /**
     * @var string
     */
    protected $matchOption = '';

    /**
     * @var string
     */
    protected $updateRule = '';

    /**
     * @var string
     */
    protected $deleteRule = '';

    /**
     * @var string
     */
    protected $checkClause = '';

    /**
     * Constructor
     *
     * @param string $name
     * @param string $tableName
     * @param string $schemaName
     */
    public function __construct(string $name, string $tableName, string $schemaName = '')
    {
        $this->setName($name);
        $this->setTableName($tableName);
        $this->setSchemaName($schemaName);
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set schema name
     *
     * @param string $schemaName
     */
    public function setSchemaName($schemaName) : void
    {
        $this->schemaName = $schemaName;
    }

    /**
     * Get schema name
     *
     * @return string
     */
    public function getSchemaName() : string
    {
        return $this->schemaName;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName() : string
    {
        return $this->tableName;
    }

    /**
     * Set table name
     *
     * @param  string $tableName
     * @return self Provides a fluent interface
     */
    public function setTableName($tableName) : self
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type) : void
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    public function hasColumns() : bool
    {
        return ! empty($this->columns);
    }

    /**
     * Get Columns.
     *
     * @return string[]
     */
    public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * Set Columns.
     *
     * @param string[] $columns
     * @return self Provides a fluent interface
     */
    public function setColumns(array $columns) : self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Get Referenced Table Schema.
     *
     * @return string
     */
    public function getReferencedTableSchema() : string
    {
        return $this->referencedTableSchema;
    }

    /**
     * Set Referenced Table Schema.
     *
     * @param string $referencedTableSchema
     * @return self Provides a fluent interface
     */
    public function setReferencedTableSchema($referencedTableSchema) : self
    {
        $this->referencedTableSchema = $referencedTableSchema;
        return $this;
    }

    /**
     * Get Referenced Table Name.
     *
     * @return string
     */
    public function getReferencedTableName() : string
    {
        return $this->referencedTableName;
    }

    /**
     * Set Referenced Table Name.
     *
     * @param string $referencedTableName
     * @return self Provides a fluent interface
     */
    public function setReferencedTableName($referencedTableName) : self
    {
        $this->referencedTableName = $referencedTableName;
        return $this;
    }

    /**
     * Get Referenced Columns.
     *
     * @return string[]
     */
    public function getReferencedColumns() : array
    {
        return $this->referencedColumns;
    }

    /**
     * Set Referenced Columns.
     *
     * @param string[] $referencedColumns
     * @return self Provides a fluent interface
     */
    public function setReferencedColumns(array $referencedColumns) : self
    {
        $this->referencedColumns = $referencedColumns;
        return $this;
    }

    /**
     * Get Match Option.
     *
     * @return string
     */
    public function getMatchOption() : string
    {
        return $this->matchOption;
    }

    /**
     * Set Match Option.
     *
     * @param string $matchOption
     * @return self Provides a fluent interface
     */
    public function setMatchOption($matchOption) : self
    {
        $this->matchOption = $matchOption;
        return $this;
    }

    /**
     * Get Update Rule.
     *
     * @return string
     */
    public function getUpdateRule() : string
    {
        return $this->updateRule;
    }

    /**
     * Set Update Rule.
     *
     * @param string $updateRule
     * @return self Provides a fluent interface
     */
    public function setUpdateRule($updateRule) : self
    {
        $this->updateRule = $updateRule;
        return $this;
    }

    /**
     * Get Delete Rule.
     *
     * @return string
     */
    public function getDeleteRule() : string
    {
        return $this->deleteRule;
    }

    /**
     * Set Delete Rule.
     *
     * @param string $deleteRule
     * @return self Provides a fluent interface
     */
    public function setDeleteRule($deleteRule) : self
    {
        $this->deleteRule = $deleteRule;
        return $this;
    }

    /**
     * Get Check Clause.
     *
     * @return string
     */
    public function getCheckClause() : string
    {
        return $this->checkClause;
    }

    /**
     * Set Check Clause.
     *
     * @param string $checkClause
     * @return self Provides a fluent interface
     */
    public function setCheckClause($checkClause) : self
    {
        $this->checkClause = $checkClause;
        return $this;
    }

    /**
     * Is primary key
     *
     * @return bool
     */
    public function isPrimaryKey() : bool
    {
        return ('PRIMARY KEY' === $this->type);
    }

    /**
     * Is unique key
     *
     * @return bool
     */
    public function isUnique() : bool
    {
        return ('UNIQUE' === $this->type);
    }

    /**
     * Is foreign key
     *
     * @return bool
     */
    public function isForeignKey() : bool
    {
        return ('FOREIGN KEY' === $this->type);
    }

    /**
     * Is foreign key
     *
     * @return bool
     */
    public function isCheck() : bool
    {
        return ('CHECK' === $this->type);
    }
}
