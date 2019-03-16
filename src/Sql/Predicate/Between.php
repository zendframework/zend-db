<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\AbstractExpression;

class Between extends AbstractExpression implements PredicateInterface
{
    protected $specification = '%1$s BETWEEN %2$s AND %3$s';
    protected $identifier;
    protected $minValue;
    protected $maxValue;

    /**
     * Constructor
     *
     * @param null|string $identifier
     * @param null|int|float|string $minValue
     * @param null|int|float|string $maxValue
     */
    public function __construct(?string $identifier = null, $minValue = null, $maxValue = null)
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

    public function setIdentifier(string $identifier) : self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier() : ?string
    {
        return $this->identifier;
    }

    /**
     * Set minimum boundary for comparison
     *
     * @param int|float|string $minValue
     * @return self
     */
    public function setMinValue($minValue) : self
    {
        $this->minValue = $minValue;

        return $this;
    }

    /**
     * Get minimum boundary for comparison
     * @return null|int|float|string
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /**
     * Set maximum boundary for comparison
     *
     * @param int|float|string $maxValue
     * @return self
     */
    public function setMaxValue($maxValue) : self
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    /**
     * Get maximum boundary for comparison
     * @return null|int|float|string
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }

    public function setSpecification(string $specification) : self
    {
        $this->specification = $specification;

        return $this;
    }

    public function getSpecification() : string
    {
        return $this->specification;
    }

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
