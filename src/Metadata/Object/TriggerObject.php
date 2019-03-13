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
    /** @var string */
    protected $name = '';

    /** @var string */
    protected $eventManipulation = '';

    /** @var string */
    protected $eventObjectCatalog = '';

    /** @var string */
    protected $eventObjectSchema = '';

    /** @var string */
    protected $eventObjectTable = '';

    /** @var string */
    protected $actionOrder = '';

    /** @var string */
    protected $actionCondition = '';

    /** @var string */
    protected $actionStatement = '';

    /** @var string */
    protected $actionOrientation = '';

    /** @var string */
    protected $actionTiming = '';

    /** @var string */
    protected $actionReferenceOldTable = '';

    /** @var string */
    protected $actionReferenceNewTable = '';

    /** @var string */
    protected $actionReferenceOldRow = '';

    /** @var string */
    protected $actionReferenceNewRow = '';

    /** @var DateTime|null */
    protected $created;

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function getEventManipulation() : string
    {
        return $this->eventManipulation;
    }

    public function setEventManipulation(string $eventManipulation) : self
    {
        $this->eventManipulation = $eventManipulation;
        return $this;
    }

    public function getEventObjectCatalog() : string
    {
        return $this->eventObjectCatalog;
    }

    public function setEventObjectCatalog(string $eventObjectCatalog) : self
    {
        $this->eventObjectCatalog = $eventObjectCatalog;
        return $this;
    }

    public function getEventObjectSchema() : string
    {
        return $this->eventObjectSchema;
    }

    public function setEventObjectSchema(string $eventObjectSchema) : self
    {
        $this->eventObjectSchema = $eventObjectSchema;
        return $this;
    }

    public function getEventObjectTable() : string
    {
        return $this->eventObjectTable;
    }

    public function setEventObjectTable(string $eventObjectTable) : self
    {
        $this->eventObjectTable = $eventObjectTable;
        return $this;
    }

    public function getActionOrder() : string
    {
        return $this->actionOrder;
    }

    public function setActionOrder(string $actionOrder) : self
    {
        $this->actionOrder = $actionOrder;
        return $this;
    }

    public function getActionCondition() : string
    {
        return $this->actionCondition;
    }

    public function setActionCondition(string $actionCondition) : self
    {
        $this->actionCondition = $actionCondition;
        return $this;
    }

    public function getActionStatement() : string
    {
        return $this->actionStatement;
    }

    public function setActionStatement(string $actionStatement) : self
    {
        $this->actionStatement = $actionStatement;
        return $this;
    }

    public function getActionOrientation() : string
    {
        return $this->actionOrientation;
    }

    public function setActionOrientation(string $actionOrientation) : self
    {
        $this->actionOrientation = $actionOrientation;
        return $this;
    }

    public function getActionTiming() : string
    {
        return $this->actionTiming;
    }

    public function setActionTiming(string $actionTiming) : self
    {
        $this->actionTiming = $actionTiming;
        return $this;
    }

    public function getActionReferenceOldTable() : string
    {
        return $this->actionReferenceOldTable;
    }

    public function setActionReferenceOldTable(string $actionReferenceOldTable) : self
    {
        $this->actionReferenceOldTable = $actionReferenceOldTable;
        return $this;
    }

    public function getActionReferenceNewTable() : string
    {
        return $this->actionReferenceNewTable;
    }

    public function setActionReferenceNewTable(string $actionReferenceNewTable) : self
    {
        $this->actionReferenceNewTable = $actionReferenceNewTable;
        return $this;
    }

    public function getActionReferenceOldRow() : string
    {
        return $this->actionReferenceOldRow;
    }

    public function setActionReferenceOldRow(string $actionReferenceOldRow) : self
    {
        $this->actionReferenceOldRow = $actionReferenceOldRow;
        return $this;
    }

    public function getActionReferenceNewRow() : string
    {
        return $this->actionReferenceNewRow;
    }

    public function setActionReferenceNewRow(string $actionReferenceNewRow) : self
    {
        $this->actionReferenceNewRow = $actionReferenceNewRow;
        return $this;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function setCreated(?DateTime $created) : self
    {
        $this->created = $created;
        return $this;
    }
}
