<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata\Object;

use DateTime;

class TriggerObject
{
    /**
     *
     *
     * @var string
     */
    protected $name = '';

    /**
     *
     *
     * @var string
     */
    protected $eventManipulation = '';

    /**
     *
     *
     * @var string
     */
    protected $eventObjectCatalog = '';

    /**
     *
     *
     * @var string
     */
    protected $eventObjectSchema = '';

    /**
     *
     *
     * @var string
     */
    protected $eventObjectTable = '';

    /**
     *
     *
     * @var string
     */
    protected $actionOrder = '';

    /**
     *
     *
     * @var string
     */
    protected $actionCondition = '';

    /**
     *
     *
     * @var string
     */
    protected $actionStatement = '';

    /**
     *
     *
     * @var string
     */
    protected $actionOrientation = '';

    /**
     *
     *
     * @var string
     */
    protected $actionTiming = '';

    /**
     *
     *
     * @var string
     */
    protected $actionReferenceOldTable = '';

    /**
     *
     *
     * @var string
     */
    protected $actionReferenceNewTable = '';

    /**
     *
     *
     * @var string
     */
    protected $actionReferenceOldRow = '';

    /**
     *
     *
     * @var string
     */
    protected $actionReferenceNewRow = '';

    /**
     *
     *
     * @var DateTime|null
     */
    protected $created;

    /**
     * Get Name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set Name.
     *
     * @param string $name
     * @return self Provides a fluent interface
     */
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get Event Manipulation.
     *
     * @return string
     */
    public function getEventManipulation() : string
    {
        return $this->eventManipulation;
    }

    /**
     * Set Event Manipulation.
     *
     * @param string $eventManipulation
     * @return self Provides a fluent interface
     */
    public function setEventManipulation(string $eventManipulation) : self
    {
        $this->eventManipulation = $eventManipulation;
        return $this;
    }

    /**
     * Get Event Object Catalog.
     *
     * @return string
     */
    public function getEventObjectCatalog() : string
    {
        return $this->eventObjectCatalog;
    }

    /**
     * Set Event Object Catalog.
     *
     * @param string $eventObjectCatalog
     * @return self Provides a fluent interface
     */
    public function setEventObjectCatalog(string $eventObjectCatalog) : self
    {
        $this->eventObjectCatalog = $eventObjectCatalog;
        return $this;
    }

    /**
     * Get Event Object Schema.
     *
     * @return string
     */
    public function getEventObjectSchema() : string
    {
        return $this->eventObjectSchema;
    }

    /**
     * Set Event Object Schema.
     *
     * @param string $eventObjectSchema
     * @return self Provides a fluent interface
     */
    public function setEventObjectSchema(string $eventObjectSchema) : self
    {
        $this->eventObjectSchema = $eventObjectSchema;
        return $this;
    }

    /**
     * Get Event Object Table.
     *
     * @return string
     */
    public function getEventObjectTable() : string
    {
        return $this->eventObjectTable;
    }

    /**
     * Set Event Object Table.
     *
     * @param string $eventObjectTable
     * @return self Provides a fluent interface
     */
    public function setEventObjectTable(string $eventObjectTable) : self
    {
        $this->eventObjectTable = $eventObjectTable;
        return $this;
    }

    /**
     * Get Action Order.
     *
     * @return string
     */
    public function getActionOrder() : string
    {
        return $this->actionOrder;
    }

    /**
     * Set Action Order.
     *
     * @param string $actionOrder
     * @return self Provides a fluent interface
     */
    public function setActionOrder(string $actionOrder) : self
    {
        $this->actionOrder = $actionOrder;
        return $this;
    }

    /**
     * Get Action Condition.
     *
     * @return string
     */
    public function getActionCondition() : string
    {
        return $this->actionCondition;
    }

    /**
     * Set Action Condition.
     *
     * @param string $actionCondition
     * @return self Provides a fluent interface
     */
    public function setActionCondition(string $actionCondition) : self
    {
        $this->actionCondition = $actionCondition;
        return $this;
    }

    /**
     * Get Action Statement.
     *
     * @return string
     */
    public function getActionStatement() : string
    {
        return $this->actionStatement;
    }

    /**
     * Set Action Statement.
     *
     * @param string $actionStatement
     * @return self Provides a fluent interface
     */
    public function setActionStatement(string $actionStatement) : self
    {
        $this->actionStatement = $actionStatement;
        return $this;
    }

    /**
     * Get Action Orientation.
     *
     * @return string
     */
    public function getActionOrientation() : string
    {
        return $this->actionOrientation;
    }

    /**
     * Set Action Orientation.
     *
     * @param string $actionOrientation
     * @return self Provides a fluent interface
     */
    public function setActionOrientation(string $actionOrientation) : self
    {
        $this->actionOrientation = $actionOrientation;
        return $this;
    }

    /**
     * Get Action Timing.
     *
     * @return string
     */
    public function getActionTiming() : string
    {
        return $this->actionTiming;
    }

    /**
     * Set Action Timing.
     *
     * @param string $actionTiming
     * @return self Provides a fluent interface
     */
    public function setActionTiming(string $actionTiming) : self
    {
        $this->actionTiming = $actionTiming;
        return $this;
    }

    /**
     * Get Action Reference Old Table.
     *
     * @return string
     */
    public function getActionReferenceOldTable() : string
    {
        return $this->actionReferenceOldTable;
    }

    /**
     * Set Action Reference Old Table.
     *
     * @param string $actionReferenceOldTable
     * @return self Provides a fluent interface
     */
    public function setActionReferenceOldTable(string $actionReferenceOldTable) : self
    {
        $this->actionReferenceOldTable = $actionReferenceOldTable;
        return $this;
    }

    /**
     * Get Action Reference New Table.
     *
     * @return string
     */
    public function getActionReferenceNewTable() : string
    {
        return $this->actionReferenceNewTable;
    }

    /**
     * Set Action Reference New Table.
     *
     * @param string $actionReferenceNewTable
     * @return self Provides a fluent interface
     */
    public function setActionReferenceNewTable(string $actionReferenceNewTable) : self
    {
        $this->actionReferenceNewTable = $actionReferenceNewTable;
        return $this;
    }

    /**
     * Get Action Reference Old Row.
     *
     * @return string
     */
    public function getActionReferenceOldRow() : string
    {
        return $this->actionReferenceOldRow;
    }

    /**
     * Set Action Reference Old Row.
     *
     * @param string $actionReferenceOldRow
     * @return self Provides a fluent interface
     */
    public function setActionReferenceOldRow(string $actionReferenceOldRow) : self
    {
        $this->actionReferenceOldRow = $actionReferenceOldRow;
        return $this;
    }

    /**
     * Get Action Reference New Row.
     *
     * @return string
     */
    public function getActionReferenceNewRow() : string
    {
        return $this->actionReferenceNewRow;
    }

    /**
     * Set Action Reference New Row.
     *
     * @param string $actionReferenceNewRow
     * @return self Provides a fluent interface
     */
    public function setActionReferenceNewRow(string $actionReferenceNewRow) : self
    {
        $this->actionReferenceNewRow = $actionReferenceNewRow;
        return $this;
    }

    /**
     * Get Created.
     *
     * @return DateTime
     */
    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    /**
     * Set Created.
     *
     * @param DateTime $created
     * @return self Provides a fluent interface
     */
    public function setCreated(?DateTime $created) : self
    {
        $this->created = $created;
        return $this;
    }
}
