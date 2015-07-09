<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\SelectableInterface;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\SqlObjectInterface;

abstract class AbstractSqlBuilder extends AbstractBuilder
{
    protected $specifications = [];

    /**
     * @var Builder
     */
    protected $platformBuilder;

    /**
     * @param Builder $platformBuilder
     */
    public function __construct(Builder $platformBuilder)
    {
        $this->platformBuilder = $platformBuilder;
    }

    protected function buildSqlString($sqlObject, Context $context)
    {
        if ($sqlObject instanceof ExpressionInterface) {
            return $this->buildExpression($sqlObject, $context);
        }
        $sqls       = [];
        $parameters = [];

        foreach ($this->specifications as $name => $specification) {
            $parameters[$name] = $this->{'build_' . $name}($sqlObject, $context, $sqls, $parameters);

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
     * @param string|array $specifications
     * @param string|array $parameters
     *
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    protected function createSqlFromSpecificationAndParameters($specifications, $parameters)
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

        if (!isset($specificationString)) {
            throw new Exception\RuntimeException(
                'A number of parameters was found that is not supported by this specification'
            );
        }

        $topParameters = [];
        foreach ($parameters as $position => $paramsForPosition) {
            if (isset($paramSpecs[$position]['combinedby'])) {
                $multiParamValues = [];
                foreach ($paramsForPosition as $multiParamsForPosition) {
                    $ppCount = count($multiParamsForPosition);
                    if (!isset($paramSpecs[$position][$ppCount])) {
                        throw new Exception\RuntimeException(sprintf(
                            'A number of parameters (%d) was found that is not supported by this specification', $ppCount
                        ));
                    }
                    $multiParamValues[] = vsprintf($paramSpecs[$position][$ppCount], $multiParamsForPosition);
                }
                $topParameters[] = implode($paramSpecs[$position]['combinedby'], $multiParamValues);
            } elseif ($paramSpecs[$position] !== null) {
                $ppCount = count($paramsForPosition);
                if (!isset($paramSpecs[$position][$ppCount])) {
                    throw new Exception\RuntimeException(sprintf(
                        'A number of parameters (%d) was found that is not supported by this specification', $ppCount
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
     * @param string|TableIdentifier|Select $table
     * @param Context $context
     * @return string
     */
    protected function resolveTable($table, Context $context)
    {
        $schema = null;
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema();
        }

        if ($table instanceof SelectableInterface) {
            $table = '(' . $this->buildSubSelect($table, $context) . ')';
        } elseif ($table) {
            $table = $context->getPlatform()->quoteIdentifier($table);
        }

        if ($schema && $table) {
            $table = $context->getPlatform()->quoteIdentifier($schema) . $context->getPlatform()->getIdentifierSeparator() . $table;
        }
        return $table;
    }

    protected function buildSubSelect(SelectableInterface $subselect, Context $context)
    {
        $context->startPrefix('subselect');

        $builder = $this->platformBuilder->getPlatformBuilder($subselect, $context->getPlatform());
        $result = $builder->buildSqlString($subselect, $context);

        $context->endPrefix();

        return $result;
    }

    private function buildExpression(ExpressionInterface $expression, Context $context)
    {
        $sql = '';

        $parts = $this->platformBuilder->getPlatformBuilder($expression, $context)->getExpressionData($expression, $context);

        foreach ($parts as $part) {
            // #7407: use $expression->getExpression() to get the unescaped
            // version of the expression
            if (is_string($part) && $expression instanceof Expression) {
                $sql .= $expression->getExpression();

                continue;
            }

            // if it is a string, simply tack it onto the return sql
            // "specification" string
            if (is_string($part)) {
                $sql .= $part;

                continue;
            }

            if (! is_array($part)) {
                throw new Exception\RuntimeException(
                    'Elements returned from getExpressionData() array must be a string or array.'
                );
            }

            // build_ values and types (the middle and last position of the
            // expression data)
            $parameters = $part[1];
            foreach ($parameters as $pIndex => &$parameter) {
                $value = $parameter->getValue();
                $type  = $parameter->getType();
                if ($value instanceof SelectableInterface) {
                    $parameter = '(' . $this->buildSubSelect($value, $context) . ')';
                } elseif ($value instanceof ExpressionInterface) {
                    $parameter = $this->buildSqlString($value, $context);
                } elseif ($type == ExpressionInterface::TYPE_IDENTIFIER) {
                    $parameter = $context->getPlatform()->quoteIdentifierInFragment($value);
                } elseif ($type == ExpressionInterface::TYPE_VALUE) {
                    $parameter = $context->getPlatform()->quoteValue($value);
                    if ($context->getParameterContainer()) {
                        $name = $context->getNestedAlias('expr');
                        $context->getParameterContainer()->offsetSet($name, $value);
                        $parameter = $context->getDriver()->formatParameterName($name);
                    }
                } elseif ($type == ExpressionInterface::TYPE_LITERAL) {
                    $parameter = $value;
                }
            }

            $sql .= vsprintf($part[0], $parameters);
        }

        return $sql;
    }

    /**
     * @param string|array $column
     * @param Context $context
     * @return string
     */
    protected function resolveColumnValue($column, Context $context)
    {
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
            return $this->buildSqlString($column, $context);
        }
        if ($column instanceof SelectableInterface) {
            return '(' . $this->buildSubSelect($column, $context) . ')';
        }
        if ($column === null) {
            return 'NULL';
        }
        return $isIdentifier
                ? $fromTable . $context->getPlatform()->quoteIdentifierInFragment($column)
                : $context->getPlatform()->quoteValue($column);
    }

    protected function renderTable($table, $alias = null)
    {
        return $table . ($alias ? ' AS ' . $alias : '');
    }

    /**
     * @param SqlObjectInterface $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Joins(SqlObjectInterface $sqlObject, Context $context)
    {
        if (!$sqlObject->joins || $sqlObject->joins->count() == 0) {
            return;
        }

        // build_ joins
        $joinSpecArgArray = [];
        foreach ($sqlObject->joins as $j => $join) {
            $context->startPrefix('join');
            $joinName = null;
            $joinAs = null;

            // table name
            if (is_array($join['name'])) {
                $joinName = current($join['name']);
                $joinAs = $context->getPlatform()->quoteIdentifier(key($join['name']));
            } else {
                $joinName = $join['name'];
            }
            if ($joinName instanceof ExpressionInterface) {
                $joinName = $joinName->getExpression();
            } elseif ($joinName instanceof TableIdentifier) {
                $joinName = $joinName->getTableAndSchema();
                $joinName = ($joinName[1] ? $context->getPlatform()->quoteIdentifier($joinName[1]) . $context->getPlatform()->getIdentifierSeparator() : '') . $context->getPlatform()->quoteIdentifier($joinName[0]);
            } elseif ($joinName instanceof SelectableInterface) {
                $joinName = '(' . $this->buildSubSelect($joinName, $context) . ')';
            } elseif (is_string($joinName) || (is_object($joinName) && is_callable([$joinName, '__toString']))) {
                $joinName = $context->getPlatform()->quoteIdentifier($joinName);
            } else {
                throw new Exception\InvalidArgumentException(sprintf('Join name expected to be Expression|TableIdentifier|Select|string, "%s" given', gettype($joinName)));
            }
            $joinSpecArgArray[$j] = [
                strtoupper($join['type']),
                $this->renderTable($joinName, $joinAs),
            ];
            $joinSpecArgArray[$j][] = ($join['on'] instanceof ExpressionInterface)
                ? $this->buildSqlString($join['on'], $context, 'join' . ($j+1) . 'part')
                : $context->getPlatform()->quoteIdentifierInFragment($join['on'], ['=', 'AND', 'OR', '(', ')', 'BETWEEN', '<', '>']); // on
            $context->endPrefix();
        }

        return [$joinSpecArgArray];
    }
}
