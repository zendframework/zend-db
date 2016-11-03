<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl\Constraint;

class Check extends AbstractConstraint
{
    /**
     * @var string|\Zend\Db\Sql\ExpressionInterface
     */
    protected $expression;

    /**
     * @param  string|\Zend\Db\Sql\ExpressionInterface $expression
     * @param  null|string $name
     */
    public function __construct($expression, $name)
    {
        $this->expression = $expression;
        $this->name       = $name;
    }

    /**
     * @param string|\Zend\Db\Sql\ExpressionInterface $expression
     * @return \Zend\Db\Sql\Ddl\Constraint\Check
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * @return string|\Zend\Db\Sql\ExpressionInterface
     */
    public function getExpression()
    {
        return $this->expression;
    }
}
