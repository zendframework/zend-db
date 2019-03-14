<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\ResultSet;

use ArrayObject;
use Zend\Hydrator\ArraySerializable;
use Zend\Hydrator\ArraySerializableHydrator;
use Zend\Hydrator\HydratorInterface;

class HydratingResultSet extends AbstractResultSet
{
    /** @var HydratorInterface */
    protected $hydrator;

    /** @var null|object */
    protected $objectPrototype;

    public function __construct(?HydratorInterface $hydrator = null, ?object $objectPrototype = null)
    {
        $defaultHydratorClass = class_exists(ArraySerializableHydrator::class)
            ? ArraySerializableHydrator::class
            : ArraySerializable::class;
        $this->setHydrator($hydrator ?: new $defaultHydratorClass());
        $this->setObjectPrototype($objectPrototype ?: new ArrayObject);
    }

    public function setObjectPrototype(object $objectPrototype) : self
    {
        $this->objectPrototype = $objectPrototype;
        return $this;
    }

    public function getObjectPrototype() : object
    {
        return $this->objectPrototype;
    }

    public function setHydrator(HydratorInterface $hydrator) : self
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    public function getHydrator() : HydratorInterface
    {
        return $this->hydrator;
    }

    /**
     * Iterator: get current item
     *
     * @return null|array|bool
     */
    public function current()
    {
        if ($this->buffer === null) {
            $this->buffer = -2; // implicitly disable buffering from here on
        } elseif (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return $this->buffer[$this->position];
        }
        $data = $this->dataSource->current();
        $object = is_array($data) ? $this->hydrator->hydrate($data, clone $this->objectPrototype) : false;

        if (is_array($this->buffer)) {
            $this->buffer[$this->position] = $object;
        }

        return $object;
    }

    public function toArray() : array
    {
        $return = [];
        foreach ($this as $row) {
            $return[] = $this->hydrator->extract($row);
        }
        return $return;
    }
}
