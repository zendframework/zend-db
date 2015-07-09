<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Stdlib\ArrayUtils;

class Expression implements ExpressionInterface
{
    /**
     * @var string
     */
    protected $expression = '';

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string $expression
     * @param string|array $valueParameter
     */
    public function __construct($expression = '', $valueParameter = null /*[, $valueParameter, ... ]*/)
    {
        if ($expression !== '') {
            $this->setExpression($expression);
        }

        $this->setParameters(is_array($valueParameter) ? $valueParameter : array_slice(func_get_args(), 1));
    }

    /**
     * @param string $expression
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setExpression($expression)
    {
        if (!is_string($expression) || $expression == '') {
            throw new Exception\InvalidArgumentException('Supplied expression must be a string.');
        }
        $this->expression = $expression;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param $parameters
     * @return Expression
     * @throws Exception\InvalidArgumentException
     */
    public function setParameters($parameters)
    {
        if (!is_scalar($parameters) && !is_array($parameters)) {
            throw new Exception\InvalidArgumentException('Expression parameters must be a scalar or array.');
        }
        $this->parameters = [];

        $parameters = (array)$parameters;
        if (ArrayUtils::hasStringKeys($parameters)) {
            foreach ($parameters as $value => $type) {
                $this->parameters[] = new ExpressionParameter($value, $type);
            }
        } else {
            foreach ($parameters as $parameter) {
                $this->parameters[] = new ExpressionParameter($parameter, self::TYPE_VALUE);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
