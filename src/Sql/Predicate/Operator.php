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

class Operator implements PredicateInterface
{
    const OPERATOR_EQUAL_TO                  = '=';
    const OP_EQ                              = '=';

    const OPERATOR_NOT_EQUAL_TO              = '!=';
    const OP_NE                              = '!=';

    const OPERATOR_LESS_THAN                 = '<';
    const OP_LT                              = '<';

    const OPERATOR_LESS_THAN_OR_EQUAL_TO     = '<=';
    const OP_LTE                             = '<=';

    const OPERATOR_GREATER_THAN              = '>';
    const OP_GT                              = '>';

    const OPERATOR_GREATER_THAN_OR_EQUAL_TO  = '>=';
    const OP_GTE                             = '>=';

    /**
     * @var int|float|bool|string
     */
    protected $left;

    /**
     * @var int|float|bool|string
     */
    protected $right;

    /**
     * @var string
     */
    protected $operator = self::OPERATOR_EQUAL_TO;

    /**
     * Constructor
     *
     * @param int|float|bool|string $left
     * @param string $operator
     * @param int|float|bool|string $right
     */
    public function __construct(
        $left = null,
        $operator = self::OPERATOR_EQUAL_TO,
        $right = null
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
    }

    /**
     * Set left side of operator
     *
     * @param  int|float|bool|string $left
     *
     * @return Operator
     */
    public function setLeft($left, $type = self::TYPE_IDENTIFIER)
    {
        $this->left = new ExpressionParameter($left, $type);
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
     * Set operator string
     *
     * @param  string $operator
     * @return Operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator string
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set right side of operator
     *
     * @param  int|float|bool|string $right
     *
     * @return Operator
     */
    public function setRight($right, $type = self::TYPE_VALUE)
    {
        $this->right = new ExpressionParameter($right, $type);
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
}
