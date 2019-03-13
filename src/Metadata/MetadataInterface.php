<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata;

use Zend\Db\Metadata\Object\ColumnObject;
use Zend\Db\Metadata\Object\ConstraintKeyObject;
use Zend\Db\Metadata\Object\ConstraintObject;
use Zend\Db\Metadata\Object\TableObject;
use Zend\Db\Metadata\Object\TriggerObject;
use Zend\Db\Metadata\Object\ViewObject;

interface MetadataInterface
{
    /**
     * Get schemas.
     *
     * @return string[]
     */
    public function getSchemas() : array;

    /**
     * Get table names.
     *
     * @param null|string $schema
     * @param bool $includeViews
     * @return string[]
     */
    public function getTableNames($schema = null, $includeViews = false) : array;

    /**
     * Get tables.
     *
     * @param null|string $schema
     * @param bool $includeViews
     * @return TableObject[]
     */
    public function getTables($schema = null, $includeViews = false) : array;

    /**
     * Get table
     *
     * @param string $tableName
     * @param null|string $schema
     * @return TableObject
     */
    public function getTable($tableName, $schema = null) : TableObject;

    /**
     * Get view names
     *
     * @param null|string $schema
     * @return string[]
     */
    public function getViewNames($schema = null) : array;

    /**
     * Get views
     *
     * @param null|string $schema
     * @return ViewObject[]
     */
    public function getViews($schema = null) : array;

    /**
     * Get view
     *
     * @param string $viewName
     * @param null|string $schema
     * @return ViewObject
     */
    public function getView($viewName, $schema = null) : ViewObject;

    /**
     * Get column names
     *
     * @param string $table
     * @param null|string $schema
     * @return string[]
     */
    public function getColumnNames($table, $schema = null) : array;

    /**
     * Get columns
     *
     * @param string $table
     * @param null|string $schema
     * @return ColumnObject[]
     */
    public function getColumns($table, $schema = null) : array;

    /**
     * Get column
     *
     * @param string $columnName
     * @param string $table
     * @param null|string $schema
     * @return ColumnObject
     */
    public function getColumn($columnName, $table, $schema = null) : ColumnObject;

    /**
     * Get constraints
     *
     * @param string $table
     * @param null|string $schema
     * @return ConstraintObject[]
     */
    public function getConstraints($table, $schema = null) : array;

    /**
     * Get constraint
     *
     * @param string $constraintName
     * @param string $table
     * @param null|string $schema
     * @return ConstraintObject
     */
    public function getConstraint($constraintName, $table, $schema = null) : ConstraintObject;

    /**
     * Get constraint keys
     *
     * @param string $constraint
     * @param string $table
     * @param null|string $schema
     * @return ConstraintKeyObject[]
     */
    public function getConstraintKeys($constraint, $table, $schema = null) : array;

    /**
     * Get trigger names
     *
     * @param null|string $schema
     * @return string[]
     */
    public function getTriggerNames($schema = null) : array;

    /**
     * Get triggers
     *
     * @param null|string $schema
     * @return TriggerObject[]
     */
    public function getTriggers($schema = null) : array;

    /**
     * Get trigger
     *
     * @param string $triggerName
     * @param null|string $schema
     * @return TriggerObject
     */
    public function getTrigger($triggerName, $schema = null) : TriggerObject;
}
