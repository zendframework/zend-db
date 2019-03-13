<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Object\ColumnObject;
use Zend\Db\Metadata\Object\ConstraintObject;
use Zend\Db\Metadata\Object\TableObject;
use Zend\Db\Metadata\Object\TriggerObject;
use Zend\Db\Metadata\Object\ViewObject;

/**
 * @deprecated Use Zend\Db\Metadata\Source\Factory::createSourceFromAdapter($adapter)
 */
class Metadata implements MetadataInterface
{
    /** @var MetadataInterface */
    protected $source;

    public function __construct(Adapter $adapter)
    {
        $this->source = Source\Factory::createSourceFromAdapter($adapter);
    }

    public function getTables(?string $schema = null, bool $includeViews = false) : array
    {
        return $this->source->getTables($schema, $includeViews);
    }

    public function getViews(?string $schema = null) : array
    {
        return $this->source->getViews($schema);
    }

    public function getTriggers(?string $schema = null) : array
    {
        return $this->source->getTriggers($schema);
    }

    public function getConstraints(string $table, ?string $schema = null) : array
    {
        return $this->source->getConstraints($table, $schema);
    }

    public function getColumns(string $table, ?string $schema = null) : array
    {
        return $this->source->getColumns($table, $schema);
    }

    public function getConstraintKeys($constraint, $table, ?string $schema = null) : array
    {
        return $this->source->getConstraintKeys($constraint, $table, $schema);
    }

    public function getConstraint($constraintName, $table, ?string $schema = null) : ConstraintObject
    {
        return $this->source->getConstraint($constraintName, $table, $schema);
    }

    public function getSchemas() : array
    {
        return $this->source->getSchemas();
    }

    public function getTableNames(?string $schema = null, bool $includeViews = false) : array
    {
        return $this->source->getTableNames($schema, $includeViews);
    }

    public function getTable($tableName, ?string $schema = null) : TableObject
    {
        return $this->source->getTable($tableName, $schema);
    }

    public function getViewNames(?string $schema = null) : array
    {
        return $this->source->getViewNames($schema);
    }

    public function getView($viewName, ?string $schema = null) : ViewObject
    {
        return $this->source->getView($viewName, $schema);
    }

    public function getTriggerNames(?string $schema = null) : array
    {
        return $this->source->getTriggerNames($schema);
    }

    public function getTrigger($triggerName, ?string $schema = null) : TriggerObject
    {
        return $this->source->getTrigger($triggerName, $schema);
    }

    public function getColumnNames(string $table, ?string $schema = null) : array
    {
        return $this->source->getColumnNames($table, $schema);
    }

    public function getColumn($columnName, $table, ?string $schema = null) : ColumnObject
    {
        return $this->source->getColumn($columnName, $table, $schema);
    }
}
