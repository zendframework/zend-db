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
use function in_array;
use function is_array;

class Operator extends AbstractExpression implements PredicateInterface
{
    public const OPERATOR_EQUAL_TO                  = '=';
    public const OP_EQ                              = '=';

    public const OPERATOR_NOT_EQUAL_TO              = '!=';
    public const OP_NE                              = '!=';

    public const OPERATOR_LESS_THAN                 = '<';
    public const OP_LT                              = '<';

    public const OPERATOR_LESS_THAN_OR_EQUAL_TO     = '<=';
    public const OP_LTE                             = '<=';

    public const OPERATOR_GREATER_THAN              = '>';
    public const OP_GT                              = '>';

    public const OPERATOR_GREATER_THAN_OR_EQUAL_TO  = '>=';
    public const OP_GTE                             = '>=';

    /**
     * {@inheritDoc}
     */
    protected $allowedTypes  = [
        self::TYPE_IDENTIFIER,
        self::TYPE_VALUE,
    ];

    /** @var int|float|bool|string */
    protected $left;

    /** @var int|float|bool|string */
    protected $right;

    /** @var string */
    protected $leftType = self::TYPE_IDENTIFIER;

    /** @var string */
    protected $rightType = self::TYPE_VALUE;

    /** @var string */
    protected $operator = self::OPERATOR_EQUAL_TO;

    /**
     * Constructor
     *
     * @param int|float|bool|string $left
     * @param string $operator
     * @param int|float|bool|string $right
     * @param string $leftType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param string $rightType TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     */
    public function __construct(
        $left = null,
        string $operator = self::OPERATOR_EQUAL_TO,
        $right = null,
        string $leftType = self::TYPE_IDENTIFIER,
        string $rightType = self::TYPE_VALUE
    ) {
        if ($left !== null) {
            $this->setLeft($left);
        }

        if ($operator !== self::OPERATOR_EQUAL_TO) {
            $this->setOperator($operator);
        }

        if ($right !== null) {
            $this->setRight($right);
        }

        if ($leftType !== self::TYPE_IDENTIFIER) {
            $this->setLeftType($leftType);
        }

        if ($rightType !== self::TYPE_VALUE) {
            $this->setRightType($rightType);
        }
    }

    /**
     * Set left side of operator
     *
     * @param  int|float|bool|string $left
     *
     * @return self Provides a fluent interface
     */
    public function setLeft($left) : self
    {
        $this->left = $left;

        if (is_array($left)) {
            $left = $this->normalizeArgument($left, $this->leftType);
            $this->leftType = $left[1];
        }

        return $this;
    }

    /**
     * Get left side of operator
     *
     * @return int|float|bool|string
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set parameter type for left side of operator
     *
     * @param  string $type TYPE_IDENTIFIER or TYPE_VALUE {@see allowedTypes}
     *
     * @return self Provides a fluent interface
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setLeftType($type) : self
    {
        if (! in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided; must be of type "%s" or "%s"',
                $type,
                __CLASS__ . '::TYPE_IDENTIFIER',
                __CLASS__ . '::TYPE_VALUE'
            ));
        }

        $this->leftType = $type;

        return $this;
    }

    /**
     * Get parameter type on left side of operator
     *
     * @return string
     */
    public function getLeftType() : string
    {
        return $this->leftType;
    }

    /**
     * Set operator string
     *
     * @param  string $operator
     * @return self Provides a fluent interface
     */
    public function setOperator($operator) : self
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator string
     *
     * @return string
     */
    public function getOperator() : string
    {
        return $this->operator;
    }

    /**
     * Set right side of operator
     *
     * @param  int|float|bool|string $right
     *
     * @return self Provides a fluent interface
     */
    public function setRight($right) : self
    {
        $this->right = $right;

        if (is_array($right)) {
            $right = $this->normalizeArgument($right, $this->rightType);
            $this->rightType = $right[1];
        }

        return $this;
    }

    /**
     * Get right side of operator
     *
     * @return int|float|bool|string
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set parameter type for right side of operator
     *
     * @param  string $type TYPE_IDENTIFIER or TYPE_VALUE {@see allowedTypes}
     *
     * @return self Provides a fluent interface
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setRightType(string $type) : self
    {
        if (! in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided; must be of type "%s" or "%s"',
                $type,
                __CLASS__ . '::TYPE_IDENTIFIER',
                __CLASS__ . '::TYPE_VALUE'
            ));
        }

        $this->rightType = $type;

        return $this;
    }

    /**
     * Get parameter type on right side of operator
     *
     * @return string
     */
    public function getRightType() : string
    {
        return $this->rightType;
    }

    /**
     * Get predicate parts for where statement
     *
     * @return array
     */
    public function getExpressionData() : array
    {
        [$values[], $types[]] = $this->normalizeArgument($this->left, $this->leftType);
        [$values[], $types[]] = $this->normalizeArgument($this->right, $this->rightType);

        return [[
            '%s ' . $this->operator . ' %s',
            $values,
            $types
        ]];
    }
}
