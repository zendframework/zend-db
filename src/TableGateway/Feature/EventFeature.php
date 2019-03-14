<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\TableGateway\Feature\EventFeature\TableGatewayEvent;
use Zend\Db\TableGateway\TableGateway;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventsCapableInterface;

class EventFeature extends AbstractFeature implements
    EventFeatureEventsInterface,
    EventsCapableInterface
{
    /** @var EventManagerInterface */
    protected $eventManager;

    /** @var TableGatewayEvent */
    protected $event;

    public function __construct(
        ?EventManagerInterface $eventManager = null,
        ?TableGatewayEvent $tableGatewayEvent = null
    ) {
        $this->eventManager = $eventManager ?? new EventManager;

        $this->eventManager->addIdentifiers([
            TableGateway::class,
        ]);

        $this->event = $tableGatewayEvent ?: new TableGatewayEvent();
    }

    public function getEventManager() : EventManagerInterface
    {
        return $this->eventManager;
    }

    public function getEvent() : TableGatewayEvent
    {
        return $this->event;
    }

    /**
     * Initialize feature and trigger "preInitialize" event
     *
     * Ensures that the composed TableGateway has identifiers based on the
     * class name, and that the event target is set to the TableGateway
     * instance. It then triggers the "preInitialize" event.
     *
     * @return void
     */
    public function preInitialize() : void
    {
        if (! $this->tableGateway instanceof TableGateway) {
            $this->eventManager->addIdentifiers([get_class($this->tableGateway)]);
        }

        $this->event->setTarget($this->tableGateway);
        $this->event->setName(static::EVENT_PRE_INITIALIZE);
        $this->eventManager->triggerEvent($this->event);
    }

    public function postInitialize() : void
    {
        $this->event->setName(static::EVENT_POST_INITIALIZE);
        $this->eventManager->triggerEvent($this->event);
    }

    /**
     * Trigger the "preSelect" event
     *
     * Triggers the "preSelect" event mapping the following parameters:
     * - $select as "select"
     *
     * @param Select $select
     * @return void
     */
    public function preSelect(Select $select) : void
    {
        $this->event->setName(static::EVENT_PRE_SELECT);
        $this->event->setParams(['select' => $select]);
        $this->eventManager->triggerEvent($this->event);
    }

    /**
     * Trigger the "postSelect" event
     *
     * Triggers the "postSelect" event mapping the following parameters:
     * - $statement as "statement"
     * - $result as "result"
     * - $resultSet as "result_set"
     *
     * @param StatementInterface $statement
     * @param ResultInterface $result
     * @param ResultSetInterface $resultSet
     * @return void
     */
    public function postSelect(
        StatementInterface $statement,
        ResultInterface $result,
        ResultSetInterface $resultSet
    ) : void {
        $this->event->setName(static::EVENT_POST_SELECT);
        $this->event->setParams([
            'statement' => $statement,
            'result' => $result,
            'result_set' => $resultSet
        ]);
        $this->eventManager->triggerEvent($this->event);
    }

    /**
     * Trigger the "preInsert" event
     *
     * Triggers the "preInsert" event mapping the following parameters:
     * - $insert as "insert"
     *
     * @param Insert $insert
     * @return void
     */
    public function preInsert(Insert $insert) : void
    {
        $this->event->setName(static::EVENT_PRE_INSERT);
        $this->event->setParams(['insert' => $insert]);
        $this->eventManager->triggerEvent($this->event);
    }

    /**
     * Trigger the "postInsert" event
     *
     * Triggers the "postInsert" event mapping the following parameters:
     * - $statement as "statement"
     * - $result as "result"
     *
     * @param StatementInterface $statement
     * @param ResultInterface $result
     * @return void
     */
    public function postInsert(StatementInterface $statement, ResultInterface $result) : void
    {
        $this->event->setName(static::EVENT_POST_INSERT);
        $this->event->setParams(compact('statement', 'result'));
        $this->eventManager->triggerEvent($this->event);
    }

    /**
     * Trigger the "preUpdate" event
     *
     * Triggers the "preUpdate" event mapping the following parameters:
     * - $update as "update"
     *
     * @param Update $update
     * @return void
     */
    public function preUpdate(Update $update) : void
    {
        $this->event->setName(static::EVENT_PRE_UPDATE);
        $this->event->setParams(['update' => $update]);
        $this->eventManager->triggerEvent($this->event);
    }

    /**
     * Trigger the "postUpdate" event
     *
     * Triggers the "postUpdate" event mapping the following parameters:
     * - $statement as "statement"
     * - $result as "result"
     *
     * @param StatementInterface $statement
     * @param ResultInterface $result
     * @return void
     */
    public function postUpdate(StatementInterface $statement, ResultInterface $result) : void
    {
        $this->event->setName(static::EVENT_POST_UPDATE);
        $this->event->setParams(compact('statement', 'result'));
        $this->eventManager->triggerEvent($this->event);
    }

    /**
     * Trigger the "preDelete" event
     *
     * Triggers the "preDelete" event mapping the following parameters:
     * - $delete as "delete"
     *
     * @param Delete $delete
     * @return void
     */
    public function preDelete(Delete $delete) : void
    {
        $this->event->setName(static::EVENT_PRE_DELETE);
        $this->event->setParams(['delete' => $delete]);
        $this->eventManager->triggerEvent($this->event);
    }

    /**
     * Trigger the "postDelete" event
     *
     * Triggers the "postDelete" event mapping the following parameters:
     * - $statement as "statement"
     * - $result as "result"
     *
     * @param StatementInterface $statement
     * @param ResultInterface $result
     * @return void
     */
    public function postDelete(StatementInterface $statement, ResultInterface $result) : void
    {
        $this->event->setName(static::EVENT_POST_DELETE);
        $this->event->setParams(compact('statement', 'result'));
        $this->eventManager->triggerEvent($this->event);
    }
}
