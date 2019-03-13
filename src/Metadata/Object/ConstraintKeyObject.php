<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata\Object;

class ConstraintKeyObject
{
    const FK_CASCADE = 'CASCADE';
    const FK_SET_NULL = 'SET NULL';
    const FK_NO_ACTION = 'NO ACTION';
    const FK_RESTRICT = 'RESTRICT';
    const FK_SET_DEFAULT = 'SET DEFAULT';

    /**
     *
     * @var string
     */
    protected $columnName = '';

    /**
     *
     * @var int|null
     */
    protected $ordinalPosition;

    /**
     *
     * @var bool
     */
    protected $positionInUniqueConstraint = false;

    /**
     *
     * @var string
     */
    protected $referencedTableSchema = '';

    /**
     *
     * @var string
     */
    protected $referencedTableName = '';

    /**
     *
     * @var string
     */
    protected $referencedColumnName = '';

    /**
     *
     * @var string
     */
    protected $foreignKeyUpdateRule = '';

    /**
     *
     * @var string
     */
    protected $foreignKeyDeleteRule = '';

    /**
     * Constructor
     *
     * @param string $column
     */
    public function __construct(string $column)
    {
        $this->setColumnName($column);
    }

    /**
     * Get column name
     *
     * @return string
     */
    public function getColumnName() : string
    {
        return $this->columnName;
    }

    /**
     * Set column name
     *
     * @param  string $columnName
     * @return self Provides a fluent interface
     */
    public function setColumnName(string $columnName) : self
    {
        $this->columnName = $columnName;
        return $this;
    }

    /**
     * Get ordinal position
     *
     * @return int|null
     */
    public function getOrdinalPosition() : ?int
    {
        return $this->ordinalPosition;
    }

    /**
     * Set ordinal position
     *
     * @param  int $ordinalPosition
     * @return self Provides a fluent interface
     */
    public function setOrdinalPosition($ordinalPosition) : self
    {
        $this->ordinalPosition = $ordinalPosition;
        return $this;
    }

    /**
     * Get position in unique constraint
     *
     * @return bool
     */
    public function getPositionInUniqueConstraint() : bool
    {
        return $this->positionInUniqueConstraint;
    }

    /**
     * Set position in unique constraint
     *
     * @param  bool $positionInUniqueConstraint
     * @return self Provides a fluent interface
     */
    public function setPositionInUniqueConstraint(bool $positionInUniqueConstraint) : self
    {
        $this->positionInUniqueConstraint = $positionInUniqueConstraint;
        return $this;
    }

    /**
     * Get referenced table schema
     *
     * @return string
     */
    public function getReferencedTableSchema() : string
    {
        return $this->referencedTableSchema;
    }

    /**
     * Set referenced table schema
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
     * Get referenced table name
     *
     * @return string
     */
    public function getReferencedTableName() : string
    {
        return $this->referencedTableName;
    }

    /**
     * Set Referenced table name
     *
     * @param  string $referencedTableName
     * @return self Provides a fluent interface
     */
    public function setReferencedTableName(string $referencedTableName) : self
    {
        $this->referencedTableName = $referencedTableName;
        return $this;
    }

    /**
     * Get referenced column name
     *
     * @return string
     */
    public function getReferencedColumnName() : string
    {
        return $this->referencedColumnName;
    }

    /**
     * Set referenced column name
     *
     * @param  string $referencedColumnName
     * @return self Provides a fluent interface
     */
    public function setReferencedColumnName(string $referencedColumnName) : self
    {
        $this->referencedColumnName = $referencedColumnName;
        return $this;
    }

    /**
     * set foreign key update rule
     *
     * @param string $foreignKeyUpdateRule
     */
    public function setForeignKeyUpdateRule($foreignKeyUpdateRule) : void
    {
        $this->foreignKeyUpdateRule = $foreignKeyUpdateRule;
    }

    /**
     * Get foreign key update rule
     *
     * @return string
     */
    public function getForeignKeyUpdateRule() : string
    {
        return $this->foreignKeyUpdateRule;
    }

    /**
     * Set foreign key delete rule
     *
     * @param string $foreignKeyDeleteRule
     */
    public function setForeignKeyDeleteRule($foreignKeyDeleteRule) : void
    {
        $this->foreignKeyDeleteRule = $foreignKeyDeleteRule;
    }

    /**
     * get foreign key delete rule
     *
     * @return string
     */
    public function getForeignKeyDeleteRule() : string
    {
        return $this->foreignKeyDeleteRule;
    }
}
