<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\ExpressionParameter;

class Between implements PredicateInterface
{
    protected $identifier    = null;
    protected $minValue      = null;
    protected $maxValue      = null;

    /**
     * Constructor
     *
     * @param  string $identifier
     * @param  int|float|string $minValue
     * @param  int|float|string $maxValue
     */
    public function __construct($identifier = null, $minValue = null, $maxValue = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($minValue !== null) {
            $this->setMinValue($minValue);
        }
        if ($maxValue !== null) {
            $this->setMaxValue($maxValue);
        }
    }

    /**
     * Set identifier for comparison
     *
     * @param  string $identifier
     * @return Between
     */
    public function setIdentifier($identifier, $type = self::TYPE_IDENTIFIER)
    {
        $this->identifier = new ExpressionParameter($identifier, $type);
        return $this;
    }

    /**
     * Get identifier of comparison
     *
     * @return null|string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set minimum boundary for comparison
     *
     * @param  int|float|string $minValue
     * @return Between
     */
    public function setMinValue($minValue, $type = self::TYPE_VALUE)
    {
        $this->minValue = new ExpressionParameter($minValue, $type);
        return $this;
    }

    /**
     * Get minimum boundary for comparison
     *
     * @return null|int|float|string
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /**
     * Set maximum boundary for comparison
     *
     * @param  int|float|string $maxValue
     * @return Between
     */
    public function setMaxValue($maxValue, $type = self::TYPE_VALUE)
    {
        $this->maxValue = new ExpressionParameter($maxValue, $type);
        return $this;
    }

    /**
     * Get maximum boundary for comparison
     *
     * @return null|int|float|string
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }
}
