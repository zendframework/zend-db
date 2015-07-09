<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\SelectableInterface;
use Zend\Db\Sql\ExpressionParameter;

class In implements PredicateInterface
{
    protected $identifier;
    protected $valueSet;

    /**
     * Constructor
     *
     * @param null|string|array $identifier
     * @param null|array|Select $valueSet
     */
    public function __construct($identifier = null, $valueSet = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($valueSet) {
            $this->setValueSet($valueSet);
        }
    }

    /**
     * Set identifier for comparison
     *
     * @param  string|array $identifier
     * @return In
     */
    public function setIdentifier($identifier, $type = self::TYPE_IDENTIFIER)
    {
        if (is_array($identifier)) {
            $this->identifier = [];
            foreach ($identifier as $ident) {
                $this->identifier[] = new ExpressionParameter($ident, $type);
            }
        } else {
            $this->identifier = new ExpressionParameter($identifier, $type);
        }

        return $this;
    }

    /**
     * Get identifier of comparison
     *
     * @return null|string|array
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set set of values for IN comparison
     *
     * @param  array|SelectableInterface                       $valueSet
     * @throws Exception\InvalidArgumentException
     * @return In
     */
    public function setValueSet($valueSet)
    {
        if ($valueSet instanceof SelectableInterface) {
            $this->valueSet = new ExpressionParameter($valueSet);
        } elseif (is_array($valueSet)) {
            $this->valueSet = [];
            foreach ($valueSet as $value) {
                $this->valueSet[] = new ExpressionParameter($value, self::TYPE_VALUE);
            }
        } else {
            throw new Exception\InvalidArgumentException(
                '$valueSet must be either an array or a Zend\Db\Sql\SelectableInterface object, ' . gettype($valueSet) . ' given'
            );
        }
        return $this;
    }

    /**
     * Gets set of values in IN comparision
     *
     * @return array|Select
     */
    public function getValueSet()
    {
        return $this->valueSet;
    }
}
