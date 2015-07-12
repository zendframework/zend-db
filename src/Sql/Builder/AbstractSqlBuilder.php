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
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\SqlObjectInterface;

abstract class AbstractSqlBuilder extends AbstractBuilder
{
    protected $implodeGlueKey = 'implode_glue';

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
        if (is_array($sqlObject)) {
            return $this->createSqlFromSpecificationAndParameters($sqlObject['spec'], $sqlObject['params'], $context);
        }

        if (!$sqlObject instanceof SqlObjectInterface && !$sqlObject instanceof ExpressionInterface) {
            throw new \Zend\Db\Sql\Exception\InvalidArgumentException(sprintf(
                'Argument $sqlObject passed to %s must be an instance of %s, instance of %s given',
                __METHOD__ . '()',
                'Zend\Db\Sql\SqlObjectInterface or Zend\Db\Sql\ExpressionInterface',
                is_object($sqlObject) ? get_class($sqlObject) : gettype($sqlObject)
            ));
        }

        $specAndParams = $this
                ->platformBuilder
                ->getPlatformBuilder($sqlObject, $context->getAdapter())
                ->build($sqlObject, $context);

        if (isset($specAndParams[$this->implodeGlueKey])) {
            $implodeGlue = $specAndParams[$this->implodeGlueKey];
            unset($specAndParams[$this->implodeGlueKey]);
        } else {
            $implodeGlue = $sqlObject instanceof SqlObjectInterface ? ' ' : '';
        }

        $sqls       = [];
        foreach ($specAndParams as $spec) {
            if (!$spec) {
                continue;
            }
            if (is_scalar($spec)) {
                $sqls[] = $spec;
                continue;
            }
            if (is_array($spec)) {
                $sqls[] = $this->createSqlFromSpecificationAndParameters($spec['spec'], $spec['params'], $context);
                continue;
            }
            $sqls[] = $this->buildSpecificationParameter($spec, $context);
        }

        return rtrim(implode($implodeGlue, $sqls), "\n ,");
    }

    /*
     * $spec = array(
            'format' => ' string format : http://php.net/manual/en/function.sprintf.php',
            'byArgNumber' => array(
                'parameter index' => 'spec for parameter',
                '      ...      ' => '       ...        ',
                'parameter index' => 'spec for parameter',
            ),
            'byCount' => array(
                -1                   => 'spec if count not found or !is_array($args)',
                'count($args)' => 'spec',
                '        ...       ' => ' .. ',
                'count($args)' => 'spec',
            ),
            'implode' => 'is isset - do implode array',
        );
    */
    private function createSqlFromSpecificationAndParameters($specification, $args, Context $context = null)
    {
        if (is_string($specification)) {
            return vsprintf(
                $specification,
                $this->buildSpecificationParameter($args, $context)
            );
        }

        foreach ($specification as $specName => $spec) {
            if ($specName == 'forEach') {
                foreach ($args as $pName => &$param) {
                    $param = $this->createSqlFromSpecificationAndParameters($spec, $args[$pName], $context);
                }
            } elseif ($specName == 'byArgNumber') {
                $i = 0;
                foreach ($args as $pName => &$param) {
                    if (isset($spec[++$i])) {
                        $param = $this->createSqlFromSpecificationAndParameters($spec[$i], $args[$pName], $context);
                    }
                }
            } elseif ($specName == 'byCount') {
                $pCount = is_array($args) ? count($args) : -1;
                if (isset($spec[$pCount])) {
                    $spec = $spec[$pCount];
                } elseif (isset($spec[-1])) {
                    $spec = $spec[-1];
                } else {
                    throw new \Exception('A number of parameters (' . $pCount . ') was found that is not supported by this specification');
                }
                $args = $this->createSqlFromSpecificationAndParameters($spec, $args, $context);
            } elseif ($specName == 'implode') {
                if (is_array($spec)) {
                    $prefix = isset($spec['prefix']) ? $spec['prefix'] : '';
                    $suffix = isset($spec['suffix']) ? $spec['suffix'] : '';
                    $glue   = isset($spec['glue'])   ? $spec['glue'] : '';
                    $args   =
                            $prefix
                            . implode($glue, $this->buildSpecificationParameter($args, $context))
                            . $suffix;
                } else {
                    $args = implode($spec, $this->buildSpecificationParameter($args, $context));
                }
            } elseif ($specName == 'format') {
                $args = vsprintf(
                    $spec,
                    $this->buildSpecificationParameter($args, $context)
                );
            }
        }
        return $args;
    }

    protected function nornalizeTable($identifier, Context $context)
    {
        $schema      = null;
        $name        = null;
        $alias       = null;
        $columnAlias = null;

        if ($identifier instanceof TableIdentifier) {
            $name   = $identifier->getTable();
            $schema = $identifier->getSchema();
        } elseif (is_string($identifier)) {
            $name   = $identifier;
        } elseif (is_array($identifier)) {
            if (is_string(key($identifier))) {
                $alias = key($identifier);
                $name  = current($identifier);
            } elseif ($name) {
                $schema = isset($identifier[0]) ? $identifier[0] : null;
                $name   = isset($identifier[1]) ? $identifier[1] : null;
                $alias  = isset($identifier[2]) ? $identifier[2] : null;
            }
        }

        if ($alias) {
            $alias       = $context->getPlatform()->quoteIdentifier($alias);
            $columnAlias = $alias;
        }

        if (is_string($name)) {
            $name = $context->getPlatform()->quoteIdentifier($name);
            if ($schema) {
                $name = $context->getPlatform()->quoteIdentifier($schema)
                      . $context->getPlatform()->getIdentifierSeparator()
                      . $name;
            }
            if (!$columnAlias) {
                $columnAlias = $name;
            }
        }

        return [
            'name'        => $name,
            'alias'       => $alias,
            'columnAlias' => $columnAlias,
        ];
    }

    private function buildSubSelect(SelectableInterface $subselect, Context $context)
    {
        $context->startPrefix('subselect');

        $builder = $this->platformBuilder->getPlatformBuilder($subselect, $context->getPlatform());
        $result = '(' . $builder->buildSqlString($subselect, $context) . ')';

        $context->endPrefix();

        return $result;
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
            return $this->buildSubSelect($column, $context);
        }
        if ($column === null) {
            return 'NULL';
        }
        return $isIdentifier
                ? $fromTable . $context->getPlatform()->quoteIdentifierInFragment($column)
                : $context->getPlatform()->quoteValue($column);
    }

    private function buildSpecificationParameter($parameter, Context $context = null)
    {
        if (is_array($parameter)) {
            foreach ($parameter as &$ppp) {
                $ppp = $this->buildSpecificationParameter($ppp, $context);
            }
            return $parameter;
        }

        $isQuoted = false;
        if ($parameter instanceof ExpressionParameter) {
            $value      = $parameter->getValue();
            $type       = $parameter->getType();
            $paramName  = $parameter->getName();
            $isQuoted   = $parameter->getOption('isQuoted');
        } else {
            $value      = $parameter;
            $type       = ExpressionInterface::TYPE_LITERAL;
        }

        if ($value instanceof TableIdentifier) {
            $parameter = $this->nornalizeTable($value, $context)['name'];
        } elseif ($value instanceof SelectableInterface) {
            $parameter = $this->buildSubSelect($value, $context);
        } elseif ($value instanceof ExpressionInterface) {
            $parameter = $this->buildSqlString($value, $context);
        } elseif ($type == ExpressionInterface::TYPE_IDENTIFIER) {
            $parameter = $context->getPlatform()->quoteIdentifierInFragment($value);
        } elseif ($type == ExpressionInterface::TYPE_VALUE) {
            if ($context->getParameterContainer()) {
                $name = isset($paramName)
                        ? $paramName
                        : $context->getNestedAlias('expr');
                if (is_array($name)) {
                    $context->getParameterContainer()->offsetSet($name[0], $value);
                    $context->getParameterContainer()->offsetSetReference($name[1], $name[0]);
                } else {
                    $context->getParameterContainer()->offsetSet($name, $value);
                }
                $parameter = $context->getDriver()->formatParameterName($name);
            } else {
                $parameter = $isQuoted
                        ? $value
                        : $context->getPlatform()->quoteValue($value);
            }
        } elseif ($type == ExpressionInterface::TYPE_LITERAL) {
            $parameter = $value;
        }

        return $parameter;
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
        if (!$sqlObject->joins || !$sqlObject->joins->count()) {
            return;
        }

        // build_ joins
        $joinSpecArgArray = [];
        foreach ($sqlObject->joins as $j => $join) {
            $jTable = $this->nornalizeTable($join['name'], $context);
            unset($jTable['columnAlias']);
            if (!$jTable['alias']) {
                unset($jTable['alias']);
            }
            if (!$jTable['name']) {
                $jTable = null;
            }
            $joinSpecArgArray[$j] = [
                strtoupper($join['type']),
                $jTable,
            ];
            $joinSpecArgArray[$j][] = ($join['on'] instanceof ExpressionInterface)
                ? $join['on']
                : $context->getPlatform()->quoteIdentifierInFragment($join['on'], ['=', 'AND', 'OR', '(', ')', 'BETWEEN', '<', '>']); // on
        }
        return [
            'spec' => $this->joinsSpecification,
            'params' => $joinSpecArgArray
        ];
    }
}
