<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

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
    /**
     * @var MetadataInterface
     */
    protected $source = null;

    /**
     * Constructor
     *
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->source = Source\Factory::createSourceFromAdapter($adapter);
    }

    /**
     * {@inheritdoc}
     */
    public function getTables($schema = null, $includeViews = false) : array
    {
        return $this->source->getTables($schema, $includeViews);
    }

    /**
     * {@inheritdoc}
     */
    public function getViews($schema = null) : array
    {
        return $this->source->getViews($schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggers($schema = null) : array
    {
        return $this->source->getTriggers($schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints($table, $schema = null) : array
    {
        return $this->source->getConstraints($table, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns($table, $schema = null) : array
    {
        return $this->source->getColumns($table, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintKeys($constraint, $table, $schema = null) : array
    {
        return $this->source->getConstraintKeys($constraint, $table, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraint($constraintName, $table, $schema = null) : ConstraintObject
    {
        return $this->source->getConstraint($constraintName, $table, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemas() : array
    {
        return $this->source->getSchemas();
    }

    /**
     * {@inheritdoc}
     */
    public function getTableNames($schema = null, $includeViews = false) : array
    {
        return $this->source->getTableNames($schema, $includeViews);
    }

    /**
     * {@inheritdoc}
     */
    public function getTable($tableName, $schema = null) : TableObject
    {
        return $this->source->getTable($tableName, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewNames($schema = null) : array
    {
        return $this->source->getViewNames($schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getView($viewName, $schema = null) : ViewObject
    {
        return $this->source->getView($viewName, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggerNames($schema = null) : array
    {
        return $this->source->getTriggerNames($schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrigger($triggerName, $schema = null) : TriggerObject
    {
        return $this->source->getTrigger($triggerName, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnNames($table, $schema = null) : array
    {
        return $this->source->getColumnNames($table, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($columnName, $table, $schema = null) : ColumnObject
    {
        return $this->source->getColumn($columnName, $table, $schema);
    }
}
