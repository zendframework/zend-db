<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\AbstractExpression;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Select;
use function array_fill;
use function count;
use function gettype;
use function implode;
use function is_array;
use function vsprintf;

class In extends AbstractExpression implements PredicateInterface
{
    protected $identifier;
    protected $valueSet;

    protected $specification = '%s IN %s';

    protected $valueSpecSpecification = '%%s IN (%s)';

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

        if ($valueSet !== null) {
            $this->setValueSet($valueSet);
        }
    }

    /**
     * Set identifier for comparison
     *
     * @param string|array $identifier
     * @return $this
     */
    public function setIdentifier($identifier) : self
    {
        $this->identifier = $identifier;

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
     * @param array|Select $valueSet
     * @return $this
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setValueSet($valueSet) : self
    {
        if (! is_array($valueSet) && ! $valueSet instanceof Select) {
            throw new Exception\InvalidArgumentException(
                '$valueSet must be either an array or a Zend\Db\Sql\Select object, ' . gettype($valueSet) . ' given'
            );
        }

        $this->valueSet = $valueSet;

        return $this;
    }

    /**
     * Gets set of values in IN comparison
     * @return array|Select
     */
    public function getValueSet()
    {
        return $this->valueSet;
    }

    public function getExpressionData() : array
    {
        $identifier = $this->getIdentifier();
        $values = $this->getValueSet();
        $replacements = [];

        if (is_array($identifier)) {
            $countIdentifier = count($identifier);
            $identifierSpecFragment = '(' . implode(', ', array_fill(0, $countIdentifier, '%s')) . ')';
            $types = array_fill(0, $countIdentifier, self::TYPE_IDENTIFIER);
            $replacements = $identifier;
        } else {
            $identifierSpecFragment = '%s';
            $replacements[] = $identifier;
            $types = [self::TYPE_IDENTIFIER];
        }

        if ($values instanceof Select) {
            $specification = vsprintf(
                $this->specification,
                [$identifierSpecFragment, '%s']
            );
            $replacements[] = $values;
            $types[] = self::TYPE_VALUE;
        } else {
            foreach ($values as $argument) {
                list($replacements[], $types[]) = $this->normalizeArgument($argument, self::TYPE_VALUE);
            }
            $countValues = count($values);
            $valuePlaceholders = $countValues > 0 ? array_fill(0, $countValues, '%s') : [];
            $specification = vsprintf(
                $this->specification,
                [$identifierSpecFragment, '(' . implode(', ', $valuePlaceholders) . ')']
            );
        }

        return [[
            $specification,
            $replacements,
            $types,
        ]];
    }
}
