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
    public const FK_CASCADE = 'CASCADE';
    public const FK_SET_NULL = 'SET NULL';
    public const FK_NO_ACTION = 'NO ACTION';
    public const FK_RESTRICT = 'RESTRICT';
    public const FK_SET_DEFAULT = 'SET DEFAULT';

    /** @var string */
    protected $columnName = '';

    /** @var int|null */
    protected $ordinalPosition;

    /** @var bool */
    protected $positionInUniqueConstraint = false;

    /** @var string */
    protected $referencedTableSchema = '';

    /** @var string */
    protected $referencedTableName = '';

    /** @var string */
    protected $referencedColumnName = '';

    /** @var string */
    protected $foreignKeyUpdateRule = '';

    /** @var string */
    protected $foreignKeyDeleteRule = '';

    public function __construct(string $column)
    {
        $this->setColumnName($column);
    }

    public function getColumnName() : string
    {
        return $this->columnName;
    }

    public function setColumnName(string $columnName) : self
    {
        $this->columnName = $columnName;
        return $this;
    }

    public function getOrdinalPosition() : ?int
    {
        return $this->ordinalPosition;
    }

    public function setOrdinalPosition($ordinalPosition) : self
    {
        $this->ordinalPosition = $ordinalPosition;
        return $this;
    }

    public function getPositionInUniqueConstraint() : bool
    {
        return $this->positionInUniqueConstraint;
    }

    public function setPositionInUniqueConstraint(bool $positionInUniqueConstraint) : self
    {
        $this->positionInUniqueConstraint = $positionInUniqueConstraint;
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

    public function setReferencedTableName(string $referencedTableName) : self
    {
        $this->referencedTableName = $referencedTableName;
        return $this;
    }

    public function getReferencedColumnName() : string
    {
        return $this->referencedColumnName;
    }

    public function setReferencedColumnName(string $referencedColumnName) : self
    {
        $this->referencedColumnName = $referencedColumnName;
        return $this;
    }

    public function setForeignKeyUpdateRule($foreignKeyUpdateRule) : void
    {
        $this->foreignKeyUpdateRule = $foreignKeyUpdateRule;
    }

    public function getForeignKeyUpdateRule() : string
    {
        return $this->foreignKeyUpdateRule;
    }

    public function setForeignKeyDeleteRule($foreignKeyDeleteRule) : void
    {
        $this->foreignKeyDeleteRule = $foreignKeyDeleteRule;
    }

    public function getForeignKeyDeleteRule() : string
    {
        return $this->foreignKeyDeleteRule;
    }
}
