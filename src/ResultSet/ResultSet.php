<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\ResultSet;

use ArrayObject;

class ResultSet extends AbstractResultSet
{
    const TYPE_ARRAYOBJECT = 'arrayobject';
    const TYPE_ARRAY  = 'array';

    protected $allowedReturnTypes = [
        self::TYPE_ARRAYOBJECT,
        self::TYPE_ARRAY,
    ];

    /** @var ArrayObject */
    protected $arrayObjectPrototype;

    /**
     * Return type to use when returning an object from the set
     *
     * @var string One of the above declared TYPE constants
     */
    protected $returnType = self::TYPE_ARRAYOBJECT;

    public function __construct(string $returnType = self::TYPE_ARRAYOBJECT, ?ArrayObject $arrayObjectPrototype = null)
    {
        if (in_array($returnType, [self::TYPE_ARRAY, self::TYPE_ARRAYOBJECT])) {
            $this->returnType = $returnType;
        } else {
            $this->returnType = self::TYPE_ARRAYOBJECT;
        }
        if ($this->returnType === self::TYPE_ARRAYOBJECT) {
            $this->setArrayObjectPrototype(($arrayObjectPrototype) ?: new ArrayObject([], ArrayObject::ARRAY_AS_PROPS));
        }
    }

    public function setArrayObjectPrototype(object $arrayObjectPrototype) : self
    {
        if (! $arrayObjectPrototype instanceof ArrayObject
            && ! method_exists($arrayObjectPrototype, 'exchangeArray')
        ) {
            throw new Exception\InvalidArgumentException(
                'Object must be of type ArrayObject, or at least implement exchangeArray'
            );
        }
        $this->arrayObjectPrototype = $arrayObjectPrototype;
        return $this;
    }

    public function getArrayObjectPrototype() : ArrayObject
    {
        return $this->arrayObjectPrototype;
    }

    public function getReturnType() : string
    {
        return $this->returnType;
    }

    /**
     * @return array|ArrayObject|null
     */
    public function current()
    {
        $data = parent::current();

        if ($this->returnType === self::TYPE_ARRAYOBJECT && is_array($data)) {
            /** @var $ao ArrayObject */
            $ao = clone $this->arrayObjectPrototype;
            if ($ao instanceof ArrayObject || method_exists($ao, 'exchangeArray')) {
                $ao->exchangeArray($data);
            }
            return $ao;
        }

        return $data;
    }
}
