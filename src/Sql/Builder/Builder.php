<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Zend\Db\Adapter;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\SqlObjectInterface;
use Zend\Db\Sql\SelectableInterface;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\TableSource;
use Zend\Db\Adapter\SqlBuilderInterface;

class Builder extends AbstractBuilder implements SqlBuilderInterface
{
    /**
     * @var Adapter\AdapterInterface
     */
    protected $defaultAdapter;

    protected $buildersInstances = [];

    protected $concreteBuilders = [];

    protected $inheritableBuilders = [
        'Zend\Db\Sql\Combine'         => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\CombineBuilder',
        ],
        'Zend\Db\Sql\Delete'          => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\DeleteBuilder',
        ],
        'Zend\Db\Sql\Insert'          => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\InsertBuilder',
        ],
        'Zend\Db\Sql\Select'          => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\SelectBuilder',
            'mysql'     => 'Zend\Db\Sql\Builder\MySql\SelectBuilder',
            'ibmdb2'    => 'Zend\Db\Sql\Builder\IbmDb2\SelectBuilder',
            'oracle'    => 'Zend\Db\Sql\Builder\Oracle\SelectBuilder',
            'sqlserver' => 'Zend\Db\Sql\Builder\SqlServer\SelectBuilder',
        ],
        'Zend\Db\Sql\Update'          => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\UpdateBuilder',
        ],

        'Zend\Db\Sql\Ddl\AlterTable'  => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\Ddl\AlterTableBuilder',
        ],
        'Zend\Db\Sql\Ddl\CreateTable' => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\Ddl\CreateTableBuilder',
            'sqlserver' => 'Zend\Db\Sql\Builder\SqlServer\Ddl\CreateTableBuilder',
        ],
        'Zend\Db\Sql\Ddl\DropTable'   => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\Ddl\DropTableBuilder',
            'sqlserver' => 'Zend\Db\Sql\Builder\SqlServer\Ddl\DropTableBuilder',
        ],

        'Zend\Db\Sql\Predicate\NotBetween'      => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\NotBetweenBuilder',
        ],
        'Zend\Db\Sql\Predicate\Between'      => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\BetweenBuilder',
        ],
        'Zend\Db\Sql\Predicate\NotIn'        => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\NotInBuilder',
        ],
        'Zend\Db\Sql\Predicate\In'           => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\InBuilder',
        ],
        'Zend\Db\Sql\Predicate\IsNotNull'    => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\IsNotNullBuilder',
        ],
        'Zend\Db\Sql\Predicate\IsNull'       => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\IsNullBuilder',
        ],
        'Zend\Db\Sql\Predicate\NotLike'      => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\NotLikeBuilder',
        ],
        'Zend\Db\Sql\Predicate\Like'         => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\LikeBuilder',
        ],
        'Zend\Db\Sql\Predicate\Operator'     => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\OperatorBuilder',
        ],
        'Zend\Db\Sql\Predicate\PredicateSet' => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\PredicateSetBuilder',
        ],
        'Zend\Db\Sql\Predicate\Predicate'    => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Predicate\PredicateBuilder',
        ],
        'Zend\Db\Sql\Ddl\Column\Column'  => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Ddl\Column\ColumnBuilder',
            'mysql' => 'Zend\Db\Sql\Builder\Mysql\Ddl\Column\ColumnBuilder',
        ],
        'Zend\Db\Sql\Ddl\Index\Index' => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Ddl\Index\IndexBuilder',
        ],
        'Zend\Db\Sql\Ddl\Constraint\UniqueKey'          => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Ddl\Constraint\UniqueKeyBuilder',
        ],
        'Zend\Db\Sql\Ddl\Constraint\PrimaryKey'         => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Ddl\Constraint\PrimaryKeyBuilder',
        ],
        'Zend\Db\Sql\Ddl\Constraint\ForeignKey'         => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Ddl\Constraint\ForeignKeyBuilder',
        ],
        'Zend\Db\Sql\Ddl\Constraint\Check'              => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Ddl\Constraint\CheckBuilder',
        ],
        'Zend\Db\Sql\Ddl\Constraint\AbstractConstraint' => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\Ddl\Constraint\AbstractBuilder',
        ],
        'Zend\Db\Sql\Literal'      => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\LiteralBuilder',
        ],
        'Zend\Db\Sql\Expression'             => [
            'sql92' => 'Zend\Db\Sql\Builder\sql92\ExpressionBuilder',
        ],
    ];

    /**
     * @param Adapter\AdapterInterface $adapter
     */
    public function __construct(Adapter\AdapterInterface $adapter = null)
    {
        $this->defaultAdapter = $adapter;
    }

    /**
     * @return Adapter\AdapterInterface
     */
    public function setDefaultAdapter($adapter)
    {
        $this->defaultAdapter = $adapter;
        return $this;
    }

    /**
     * @return Adapter\AdapterInterface
     */
    public function getDefaultAdapter()
    {
        return $this->defaultAdapter;
    }

    /**
     * @param array $builders
     * @return self
     */
    public function setPlatformBuilders(array $builders)
    {
        foreach ($builders as $platform => $classes) {
            foreach ($classes as $objectClass => $builderClass) {
                $this->setPlatformBuilder($platform, $objectClass, $builderClass);
            }
        }
        return $this;
    }
    /**
     * @param string|Context|PlatformInterface|Adapter\AdapterInterface $platform
     * @param string $objectClass
     * @param string $builderClass
     * @return self
     */
    public function setPlatformBuilder($platform, $objectClass, $builderClass)
    {
        $platform = $this->resolvePlatformName($platform);
        if ($builderClass instanceof AbstractSqlBuilder) {
            $builder = get_class($builderClass);
            $this->buildersInstances[$builder] = $builderClass;
        } elseif (is_string($builderClass)) {
            $builder = $builderClass;
        } else {
            throw new Exception\InvalidArgumentException(sprintf(
                '$builderClass should be %s or %s instance',
                'string',
                'Zend\Db\Sql\Builder\AbstractSqlBuilder'
            ));
        }
        $this->inheritableBuilders[$objectClass][$platform] = $builder;
        return $this;
    }

    /**
     * @param SqlObjectInterface $sqlObject
     * @param string|Context|PlatformInterface|Adapter\AdapterInterface $platform
     * @return AbstractSqlBuilder
     * @throws Exception\RuntimeException
     */
    public function getPlatformBuilder($sqlObject, $platform = 'sql92')
    {
        $platform = $this->resolvePlatformName($platform);

        $mapName = $platform . '-' . get_class($sqlObject);
        if (array_key_exists($mapName, $this->concreteBuilders)) {
            $builder = $this->concreteBuilders[$mapName];
            if ($builder === false) {
                throw new Exception\RuntimeException(sprintf(
                    'Builder for "%s" not found',
                    get_class($sqlObject)
                ));
            }
            if (!isset($this->buildersInstances[$builder])) {
                $this->buildersInstances[$builder] = new $builder($this);
            }
            return $this->buildersInstances[$builder];
        }

        foreach ($this->inheritableBuilders as $type => $builders) {
            if (!$sqlObject instanceof $type) {
                continue;
            }
            if (!isset($builders[$platform])) {
                break;
            }
            $builder = $builders[$platform];
            if (!isset($this->concreteBuilders[$mapName])) {
                $this->concreteBuilders[$mapName] = $builder;
            }
            if (!isset($this->buildersInstances[$builder])) {
                $this->buildersInstances[$builder] = new $builder($this);
            }
            return $this->buildersInstances[$builder];
        }

        if ($platform == 'sql92') {
            throw new Exception\RuntimeException(sprintf(
                'Builder for "%s" not found',
                get_class($sqlObject)
            ));
        }
        return $this->getPlatformBuilder($sqlObject);
    }

    /**
     * @param SqlObjectInterface|ExpressionInterface $object
     * @param null|Adapter\AdapterInterface $adapter
     * @return string
     */
    public function buildSqlString($object, Adapter\AdapterInterface $adapter = null)
    {
        $adapter = $adapter ?: $this->defaultAdapter;
        return $this->build($object, new Context($adapter));
    }

    /**
     * @param SqlObjectInterface|ExpressionInterface $object
     * @param null|Adapter\AdapterInterface $adapter
     * @return \Zend\Db\Adapter\Driver\StatementInterface
     */
    public function prepareSqlStatement($object, Adapter\AdapterInterface $adapter = null)
    {
        $adapter = $adapter ?: $this->defaultAdapter;
        $statement = $adapter->getDriver()->createStatement();
        if (!$statement->getParameterContainer()) {
            $statement->setParameterContainer(new Adapter\ParameterContainer);
        }
        $statement->setSql(
            $this->build($object, new Context($adapter, $statement->getParameterContainer()))
        );

        return $statement;
    }

    /**
     * @param string|Context|PlatformInterface|Adapter\AdapterInterface $platform
     * @return string
     */
    protected function resolvePlatformName($platform)
    {
        if ($platform instanceof Context) {
            $platform = $platform->getPlatform()->getName();
        } elseif ($platform instanceof PlatformInterface) {
            $platform = $platform->getName();
        } elseif ($platform instanceof Adapter\AdapterInterface) {
            $platform = $platform->getPlatform()->getName();
        }
        return str_replace(' ', '', strtolower($platform));
    }

    /**
     * @param SqlObjectInterface|ExpressionInterface $sqlObject
     * @param Context $context
     * @return string
     * @throws \Zend\Db\Sql\Exception\InvalidArgumentException
     */
    protected function build($sqlObject, Context $context)
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
                ->getPlatformBuilder($sqlObject, $context->getAdapter())
                ->build($sqlObject, $context);

        if ($specAndParams === false) {
            return;
        }

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
            $args = $this->buildSpecificationParameter($args, $context);
            if ($args === null) {
                return;
            }
            return vsprintf(
                $specification,
                $args
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

        if ($value instanceof TableIdentifier || $value instanceof TableSource) {
            $parameter = $this->nornalizeTable($value, $context)['name'];
        } elseif ($value instanceof SelectableInterface) {
            $parameter = $this->buildSubSelect($value, $context);
        } elseif ($value instanceof ExpressionInterface) {
            $parameter = $this->build($value, $context);
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

    private function buildSubSelect(SelectableInterface $subselect, Context $context)
    {
        $context->startPrefix('subselect');

        $result = '(' . $this->build($subselect, $context) . ')';

        $context->endPrefix();

        return $result;
    }
}
