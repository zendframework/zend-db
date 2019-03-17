<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

use function count;
use function is_array;
use function is_scalar;
use function is_string;
use function preg_match_all;
use function str_ireplace;
use function str_replace;

class Expression extends AbstractExpression
{
    /**
     * @const
     */
    public const PLACEHOLDER = '?';

    /** @var string */
    protected $expression = '';

    /** @var array */
    protected $parameters = [];

    /** @var array */
    protected $types = [];

    /**
     * @param string            $expression
     * @param null|string|array $parameters
     * @param array             $types @deprecated will be dropped in version 3.0.0
     */
    public function __construct(string $expression = '', $parameters = null, array $types = [])
    {
        if ($expression !== '') {
            $this->setExpression($expression);
        }

        if ($types) { // should be deprecated and removed version 3.0.0
            if (is_array($parameters)) {
                foreach ($parameters as $i => $parameter) {
                    $parameters[$i] = [
                        $parameter => isset($types[$i]) ? $types[$i] : self::TYPE_VALUE,
                    ];
                }
            } elseif (is_scalar($parameters)) {
                $parameters = [
                    $parameters => $types[0],
                ];
            }
        }

        if ($parameters) {
            $this->setParameters($parameters);
        }
    }

    public function setExpression(string $expression) : self
    {
        if (! is_string($expression) || $expression == '') {
            throw new Exception\InvalidArgumentException('Supplied expression must be a string.');
        }

        $this->expression = $expression;

        return $this;
    }

    public function getExpression() : string
    {
        return $this->expression;
    }

    /**
     * @param $parameters
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setParameters($parameters) : self
    {
        if (! is_scalar($parameters) && ! is_array($parameters)) {
            throw new Exception\InvalidArgumentException('Expression parameters must be a scalar or array.');
        }

        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * @deprecated
     * @param array $types
     * @return $this
     */
    public function setTypes(array $types) : self
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @deprecated
     * @return array
     */
    public function getTypes() : array
    {
        return $this->types;
    }

    public function getExpressionData() : array
    {
        $parameters      = is_scalar($this->parameters) ? [$this->parameters] : $this->parameters;
        $parametersCount = count($parameters);
        $expression      = str_replace('%', '%%', $this->expression);

        if ($parametersCount == 0) {
            return [
                str_ireplace(self::PLACEHOLDER, '', $expression)
            ];
        }

        // assign locally, escaping % signs
        $expression = str_replace(self::PLACEHOLDER, '%s', $expression, $count);

        // test number of replacements without considering same variable begin used many times first, which is
        // faster, if the test fails then resort to regex which are slow and used rarely
        if ($count !== $parametersCount && $parametersCount === preg_match_all('/\:[a-zA-Z0-9_]*/', $expression)) {
            throw new Exception\RuntimeException(
                'The number of replacements in the expression does not match the number of parameters'
            );
        }

        foreach ($parameters as $parameter) {
            list($values[], $types[]) = $this->normalizeArgument($parameter, self::TYPE_VALUE);
        }

        return [[
            $expression,
            $values,
            $types,
        ]];
    }
}
