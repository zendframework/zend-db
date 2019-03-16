<?php

declare(strict_types=1);

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Platform\Sql92 as DefaultAdapterPlatform;
use Zend\Db\Sql\Exception\InvalidArgumentException;
use Zend\Db\Sql\Exception\RuntimeException;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use function count;
use function gettype;
use function implode;
use function is_array;
use function is_string;
use function preg_replace;
use function rtrim;
use function sprintf;
use function vsprintf;

abstract class AbstractSql implements SqlInterface
{
    /**
     * Specifications for Sql String generation
     *
     * @var string[]|array[]
     */
    protected $specifications = [];

    /**
     * @var string[]
     */
    protected $processInfo = ['paramPrefix' => '', 'subselectCount' => 0];

    /**
     * @var array
     */
    protected $instanceParameterIndex = [];

    /**
     * {@inheritDoc}
     */
    public function getSqlString(?PlatformInterface $adapterPlatform = null) : string
    {
        $adapterPlatform = ($adapterPlatform) ?: new DefaultAdapterPlatform;
        return $this->buildSqlString($adapterPlatform);
    }

    /**
     * @param PlatformInterface $platform
     * @param null|DriverInterface $driver
     * @param null|ParameterContainer $parameterContainer
     *
     * @return string|void
     */
    protected function buildSqlString(
        PlatformInterface  $platform,
        DriverInterface    $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        $this->localizeVariables();

        $sqls       = [];
        $parameters = [];

        foreach ($this->specifications as $name => $specification) {
            $parameters[$name] = $this->{'process' . $name}(
                $platform,
                $driver,
                $parameterContainer,
                $sqls,
                $parameters
            );

            if ($specification && is_array($parameters[$name])) {
                $sqls[$name] = $this->createSqlFromSpecificationAndParameters($specification, $parameters[$name]);

                continue;
            }

            if (is_string($parameters[$name])) {
                $sqls[$name] = $parameters[$name];
            }
        }

        return rtrim(implode(' ', $sqls), "\n ,");
    }

    /**
     * Render table with alias in from/join parts
     *
     * @todo move TableIdentifier concatenation here
     * @param string $table
     * @param string|null $alias
     *
     * @return string
     */
    protected function renderTable(string $table, ?string $alias = null) : string
    {
        return $table . ($alias ? ' AS ' . $alias : '');
    }

    /**
     * @staticvar int $runtimeExpressionPrefix
     *
     * @param ExpressionInterface $expression
     * @param PlatformInterface $platform
     * @param null|DriverInterface $driver
     * @param null|ParameterContainer $parameterContainer
     * @param null|string $namedParameterPrefix
     *
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    protected function processExpression(
        ExpressionInterface $expression,
        PlatformInterface   $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null,
        ?string             $namedParameterPrefix = null
    ) : string {
        $namedParameterPrefix = ! $namedParameterPrefix
            ? $namedParameterPrefix
            : $this->processInfo['paramPrefix'] . $namedParameterPrefix;

        // static counter for the number of times this method was invoked across the PHP runtime
        static $runtimeExpressionPrefix = 0;

        if ($parameterContainer && (! is_string($namedParameterPrefix) || $namedParameterPrefix == '')) {
            $namedParameterPrefix = sprintf('expr%04dParam', ++$runtimeExpressionPrefix);
        } else {
            $namedParameterPrefix = preg_replace('/\s/', '__', $namedParameterPrefix);
        }

        $sql = '';

        // initialize variables
        $parts = $expression->getExpressionData();

        if (! isset($this->instanceParameterIndex[$namedParameterPrefix])) {
            $this->instanceParameterIndex[$namedParameterPrefix] = 1;
        }

        $expressionParamIndex = &$this->instanceParameterIndex[$namedParameterPrefix];

        foreach ($parts as $part) {
            // #7407: use $expression->getExpression() to get the unescaped
            // version of the expression
            if (is_string($part) && $expression instanceof Expression) {
                $sql .= $expression->getExpression();
                continue;
            }

            // If it is a string, simply tack it onto the return sql
            // "specification" string
            if (is_string($part)) {
                $sql .= $part;
                continue;
            }

            if (! is_array($part)) {
                throw new RuntimeException(
                    'Elements returned from getExpressionData() array must be a string or array.'
                );
            }

            // Process values and types (the middle and last position of the
            // expression data)
            $values = $part[1];
            $types  = isset($part[2]) ? $part[2] : [];

            foreach ($values as $vIndex => $value) {
                if (! isset($types[$vIndex])) {
                    continue;
                }
                $type = $types[$vIndex];
                if ($value instanceof Select) {
                    // process sub-select
                    $values[$vIndex] = '('
                        . $this->processSubSelect($value, $platform, $driver, $parameterContainer)
                        . ')';
                } elseif ($value instanceof ExpressionInterface) {
                    // recursive call to satisfy nested expressions
                    $values[$vIndex] = $this->processExpression(
                        $value,
                        $platform,
                        $driver,
                        $parameterContainer,
                        $namedParameterPrefix . $vIndex . 'subpart'
                    );
                } elseif ($type == ExpressionInterface::TYPE_IDENTIFIER) {
                    $values[$vIndex] = $platform->quoteIdentifierInFragment($value);
                } elseif ($type == ExpressionInterface::TYPE_VALUE) {
                    // if prepareType is set, it means that this particular value must be
                    // passed back to the statement in a way it can be used as a placeholder value
                    if ($parameterContainer) {
                        $name = $namedParameterPrefix . $expressionParamIndex++;
                        $parameterContainer->offsetSet($name, $value);
                        $values[$vIndex] = $driver->formatParameterName($name);
                        continue;
                    }

                    // if not a preparable statement, simply quote the value and move on
                    $values[$vIndex] = $platform->quoteValue($value);
                } elseif ($type == ExpressionInterface::TYPE_LITERAL) {
                    $values[$vIndex] = $value;
                }
            }

            // After looping the values, interpolate them into the sql string
            // (they might be placeholder names, or values)
            $sql .= vsprintf($part[0], $values);
        }

        return $sql;
    }

    /**
     * @param string|array $specifications
     * @param array $parameters
     *
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    protected function createSqlFromSpecificationAndParameters($specifications, array $parameters) : string
    {
        if (is_string($specifications)) {
            return vsprintf($specifications, $parameters);
        }

        $parametersCount = count($parameters);

        foreach ($specifications as $specificationString => $paramSpecs) {
            if ($parametersCount == count($paramSpecs)) {
                break;
            }

            unset($specificationString, $paramSpecs);
        }

        if (! isset($specificationString)) {
            throw new RuntimeException(
                'A number of parameters was found that is not supported by this specification'
            );
        }

        $topParameters = [];
        foreach ($parameters as $position => $paramsForPosition) {
            if (isset($paramSpecs[$position]['combinedby'])) {
                $multiParamValues = [];
                foreach ($paramsForPosition as $multiParamsForPosition) {
                    if (is_array($multiParamsForPosition)) {
                        $ppCount = count($multiParamsForPosition);
                    } else {
                        $ppCount = 1;
                    }

                    if (! isset($paramSpecs[$position][$ppCount])) {
                        throw new RuntimeException(sprintf(
                            'A number of parameters (%d) was found that is not supported by this specification',
                            $ppCount
                        ));
                    }
                    $multiParamValues[] = vsprintf($paramSpecs[$position][$ppCount], $multiParamsForPosition);
                }
                $topParameters[] = implode($paramSpecs[$position]['combinedby'], $multiParamValues);
            } elseif ($paramSpecs[$position] !== null) {
                $ppCount = count($paramsForPosition);
                if (! isset($paramSpecs[$position][$ppCount])) {
                    throw new RuntimeException(sprintf(
                        'A number of parameters (%d) was found that is not supported by this specification',
                        $ppCount
                    ));
                }
                $topParameters[] = vsprintf($paramSpecs[$position][$ppCount], $paramsForPosition);
            } else {
                $topParameters[] = $paramsForPosition;
            }
        }

        return vsprintf($specificationString, $topParameters);
    }

    /**
     * @param Select                  $subselect
     * @param PlatformInterface       $platform
     * @param null|DriverInterface    $driver
     * @param null|ParameterContainer $parameterContainer
     *
     * @return string
     */
    protected function processSubSelect(
        Select              $subselect,
        PlatformInterface   $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) : string {
        if ($this instanceof PlatformDecoratorInterface) {
            $decorator = clone $this;
            $decorator->setSubject($subselect);
        } else {
            $decorator = $subselect;
        }

        if ($parameterContainer) {
            // Track subselect prefix and count for parameters
            $processInfoContext = ($decorator instanceof PlatformDecoratorInterface) ? $subselect : $decorator;
            $this->processInfo['subselectCount']++;
            $processInfoContext->processInfo['subselectCount'] = $this->processInfo['subselectCount'];
            $processInfoContext->processInfo['paramPrefix'] = 'subselect'
                . $processInfoContext->processInfo['subselectCount'];

            $sql = $decorator->buildSqlString($platform, $driver, $parameterContainer);

            // copy count
            $this->processInfo['subselectCount'] = $decorator->processInfo['subselectCount'];

            return $sql;
        }

        return $decorator->buildSqlString($platform, $driver, $parameterContainer);
    }

    /**
     * @param Join $joins
     * @param PlatformInterface $platform
     * @param null|DriverInterface $driver
     * @param null|ParameterContainer $parameterContainer
     *
     * @return string[]|void|null
     *     otherwise
     * @throws Exception\InvalidArgumentException for invalid JOIN table names.
     */
    protected function processJoin(
        Join               $joins,
        PlatformInterface  $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) {
        if (! $joins->count()) {
            return;
        }

        // process joins
        $joinSpecArgArray = [];

        foreach ($joins->getJoins() as $j => $join) {
            $joinName = null;
            $joinAs = null;

            // table name
            if (is_array($join['name'])) {
                $joinName = current($join['name']);
                $joinAs = $platform->quoteIdentifier(key($join['name']));
            } else {
                $joinName = $join['name'];
            }

            if ($joinName instanceof Expression) {
                $joinName = $joinName->getExpression();
            } elseif ($joinName instanceof TableIdentifier) {
                $joinName = $joinName->getTableAndSchema();
                $joinName = ($joinName[1]
                        ? $platform->quoteIdentifier($joinName[1]) . $platform->getIdentifierSeparator()
                        : '') . $platform->quoteIdentifier($joinName[0]);
            } elseif ($joinName instanceof Select) {
                $joinName = '(' . $this->processSubSelect($joinName, $platform, $driver, $parameterContainer) . ')';
            } elseif (is_string($joinName) || (is_object($joinName) && is_callable([$joinName, '__toString']))) {
                $joinName = $platform->quoteIdentifier($joinName);
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Join name expected to be Expression|TableIdentifier|Select|string, "%s" given',
                    gettype($joinName)
                ));
            }

            $joinSpecArgArray[$j] = [
                strtoupper($join['type']),
                $this->renderTable($joinName, $joinAs),
            ];

            // on expression
            // note: for Expression objects, pass them to processExpression with a prefix specific to each join
            // (used for named parameters)
            if (($join['on'] instanceof ExpressionInterface)) {
                $joinSpecArgArray[$j][] = $this->processExpression(
                    $join['on'],
                    $platform,
                    $driver,
                    $parameterContainer,
                    'join' . ($j + 1) . 'part'
                );
            } else {
                // on
                $joinSpecArgArray[$j][] = $platform->quoteIdentifierInFragment(
                    $join['on'],
                    ['=', 'AND', 'OR', '(', ')', 'BETWEEN', '<', '>']
                );
            }
        }

        return [$joinSpecArgArray];
    }

    /**
     * @param null|array|ExpressionInterface|Select $column
     * @param PlatformInterface                     $platform
     * @param null|DriverInterface                  $driver
     * @param null|ParameterContainer               $parameterContainer
     * @param null|string                           $namedParameterPrefix
     *
     * @return string
     */
    protected function resolveColumnValue(
        ?$column,
        PlatformInterface   $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null,
        ?string             $namedParameterPrefix = null
    ) : string {
        $namedParameterPrefix = ! $namedParameterPrefix
            ? $namedParameterPrefix
            : $this->processInfo['paramPrefix'] . $namedParameterPrefix;
        $isIdentifier = false;
        $fromTable = '';
        if (is_array($column)) {
            if (isset($column['isIdentifier'])) {
                $isIdentifier = (bool) $column['isIdentifier'];
            }
            if (isset($column['fromTable']) && $column['fromTable'] !== null) {
                $fromTable = $column['fromTable'];
            }
            $column = $column['column'];
        }

        if ($column instanceof ExpressionInterface) {
            return $this->processExpression($column, $platform, $driver, $parameterContainer, $namedParameterPrefix);
        }

        if ($column instanceof Select) {
            return '(' . $this->processSubSelect($column, $platform, $driver, $parameterContainer) . ')';
        }

        if ($column === null) {
            return 'NULL';
        }

        /** @var string $column */
        return $isIdentifier
                ? $fromTable . $platform->quoteIdentifierInFragment($column)
                : $platform->quoteValue($column);
    }

    /**
     * @param string|TableIdentifier|Select $table
     * @param PlatformInterface             $platform
     * @param DriverInterface               $driver
     * @param ParameterContainer            $parameterContainer
     *
     * @return string
     */
    protected function resolveTable(
        $table,
        PlatformInterface  $platform,
        DriverInterface    $driver = null,
        ParameterContainer $parameterContainer = null
    ) : string {
        $schema = null;

        if ($table instanceof TableIdentifier) {
            [$table, $schema] = $table->getTableAndSchema();
        }

        if ($table instanceof Select) {
            $table = '(' . $this->processSubselect($table, $platform, $driver, $parameterContainer) . ')';
        } elseif ($table) {
            $table = $platform->quoteIdentifier($table);
        }

        if (is_string($schema) && $table) {
            $table = $platform->quoteIdentifier($schema) . $platform->getIdentifierSeparator() . $table;
        }

        return $table;
    }

    /**
     * Copy variables from the subject into the local properties
     */
    protected function localizeVariables() : void
    {
        if (! $this instanceof PlatformDecoratorInterface) {
            return;
        }

        foreach (get_object_vars($this->subject) as $name => $value) {
            $this->{$name} = $value;
        }
    }
}
