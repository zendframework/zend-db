<?php

declare(strict_types=1);
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\AbstractExpression;

class Between extends AbstractExpression implements PredicateInterface
{
    protected $specification = '%1$s BETWEEN %2$s AND %3$s';
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
    public function __construct(string $identifier = null, $minValue = null, $maxValue = null)
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
     *
     * @return self Provides a fluent interface
     */
    public function setIdentifier(string $identifier) : self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier of comparison
     *
     * @return null|string
     */
    public function getIdentifier() : ?string
    {
        return $this->identifier;
    }

    /**
     * Set minimum boundary for comparison
     *
     * @param  int|float|string $minValue
     *
     * @return self Provides a fluent interface
     */
    public function setMinValue($minValue) : self
    {
        $this->minValue = $minValue;

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
     *
     * @return self Provides a fluent interface
     */
    public function setMaxValue($maxValue) : self
    {
        $this->maxValue = $maxValue;

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

    /**
     * Set specification string to use in forming SQL predicate
     *
     * @param  string $specification
     *
     * @return self Provides a fluent interface
     */
    public function setSpecification(string $specification) : self
    {
        $this->specification = $specification;

        return $this;
    }

    /**
     * Get specification string to use in forming SQL predicate
     *
     * @return string
     */
    public function getSpecification() : string
    {
        return $this->specification;
    }

    /**
     * Return "where" parts
     *
     * @return mixed[][]
     */
    public function getExpressionData() : array
    {
        [$values[], $types[]] = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        [$values[], $types[]] = $this->normalizeArgument($this->minValue, self::TYPE_VALUE);
        [$values[], $types[]] = $this->normalizeArgument($this->maxValue, self::TYPE_VALUE);
        return [
            [
                $this->getSpecification(),
                $values,
                $types,
            ],
        ];
    }
}
