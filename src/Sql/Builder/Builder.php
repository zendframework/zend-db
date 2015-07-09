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

class Builder extends AbstractBuilder
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
            'mysql'     => 'Zend\Db\Sql\Builder\MySql\Ddl\AlterTableBuilder',
        ],
        'Zend\Db\Sql\Ddl\CreateTable' => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\Ddl\CreateTableBuilder',
            'mysql'     => 'Zend\Db\Sql\Builder\MySql\Ddl\CreateTableBuilder',
            'sqlserver' => 'Zend\Db\Sql\Builder\SqlServer\Ddl\CreateTableBuilder',
        ],
        'Zend\Db\Sql\Ddl\DropTable'   => [
            'sql92'     => 'Zend\Db\Sql\Builder\sql92\Ddl\DropTableBuilder',
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
    public function getSqlString($object, Adapter\AdapterInterface $adapter = null)
    {
        $adapter = $adapter ?: $this->defaultAdapter;
        return $this->buildSqlString($object, new Context($adapter));
    }

    /**
     * @param SqlObjectInterface|ExpressionInterface $object
     * @param null|Adapter\AdapterInterface $adapter
     * @return \Zend\Db\Adapter\Driver\StatementInterface
     */
    public function prepareStatement($object, Adapter\AdapterInterface $adapter = null)
    {
        $adapter = $adapter ?: $this->defaultAdapter;
        $statement = $adapter->getDriver()->createStatement();
        if (!$statement->getParameterContainer()) {
            $statement->setParameterContainer(new Adapter\ParameterContainer);
        }
        $statement->setSql(
            $this->buildSqlString($object, new Context($adapter, $statement->getParameterContainer()))
        );

        return $statement;
    }

    /**
     * @param SqlObjectInterface $sqlObject
     * @param Context $context
     * @return string
     */
    protected function buildSqlString($sqlObject, Context $context)
    {
        $builder   = $this->getPlatformBuilder($sqlObject, $context);
        return $builder->buildSqlString($sqlObject, $context);
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
}
