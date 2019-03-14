<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature\EventFeature;

use ArrayAccess;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\EventManager\EventInterface;

class TableGatewayEvent implements EventInterface
{
    /** @var null|string|object|AbstractTableGateway */
    protected $target;

    protected $name = '';

    /** @var array|ArrayAccess */
    protected $params = [];

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get target/context from which event was triggered
     *
     * @return null|string|object|AbstractTableGateway
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Get parameters passed to the event
     *
     * @return array|ArrayAccess
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get a single parameter by name
     *
     * @param string $name
     * @param mixed $default Default value to return if parameter does not exist
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return (isset($this->params[$name]) ? $this->params[$name] : $default);
    }

    /**
     * Set the event name
     *
     * @param string $name
     * @return void
     */
    public function setName($name) : void
    {
        $this->name = $name;
    }

    /**
     * Set the event target/context
     *
     * @param null|string|object|AbstractTableGateway $target
     * @return void
     */
    public function setTarget($target) : void
    {
        $this->target = $target;
    }

    /**
     * Set event parameters
     *
     * @param array|ArrayAccess $params
     * @return void
     */
    public function setParams($params) : void
    {
        $this->params = $params;
    }

    /**
     * Set a single parameter by key
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setParam($name, $value) : void
    {
        $this->params[$name] = $value;
    }

    /**
     * Indicate whether or not the parent EventManagerInterface should stop propagating events
     *
     * @param bool $flag
     * @return void
     */
    public function stopPropagation($flag = true) : void
    {
    }

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function propagationIsStopped() : bool
    {
        return false;
    }
}
