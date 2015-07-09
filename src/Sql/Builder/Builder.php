<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\PreparableSqlObjectInterface;
use Zend\Db\Sql\SqlObjectInterface;

class Builder extends AbstractBuilder
{
    /**
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * @var PlatformInterface|null
     */
    protected $defaultPlatform;

    public function __construct(AdapterInterface $adapter)
    {
        $this->defaultPlatform = $adapter->getPlatform();

        $this->decorators = [
            'mysql'     => [
                'Zend\Db\Sql\Select'          => new Mysql\SelectBuilder(),
                'Zend\Db\Sql\Ddl\CreateTable' => new Mysql\Ddl\CreateTableBuilder(),
                'Zend\Db\Sql\Ddl\AlterTable'  => new Mysql\Ddl\AlterTableBuilder(),
            ],
            'sqlserver' => [
                'Zend\Db\Sql\Select'          => new SqlServer\SelectBuilder(),
                'Zend\Db\Sql\Ddl\CreateTable' => new SqlServer\Ddl\CreateTableBuilder(),
            ],
            'oracle'    => [
                'Zend\Db\Sql\Select'          => new Oracle\SelectBuilder(),
            ],
            'ibmdb2'    => [
                'Zend\Db\Sql\Select'          => new IbmDb2\SelectBuilder(),
            ],
            'sqlite'    => [
                'Zend\Db\Sql\Select'          => new Sqlite\SelectBuilder(),
            ],
        ];
    }

    /**
     * @param string                             $type
     * @param PlatformDecoratorInterface         $decorator
     * @param AdapterInterface|PlatformInterface $adapterOrPlatform
     */
    public function setTypeDecorator($type, PlatformDecoratorInterface $decorator, $adapterOrPlatform = null)
    {
        $platformName = $this->resolvePlatformName($adapterOrPlatform);
        $this->decorators[$platformName][$type] = $decorator;
    }

    /**
     * @param PreparableSqlObjectInterface|SqlObjectInterface     $subject
     * @param AdapterInterface|PlatformInterface|null $adapterOrPlatform
     * @return PlatformDecoratorInterface|PreparableSqlObjectInterface|SqlObjectInterface
     */
    public function getTypeDecorator($subject, $adapterOrPlatform = null)
    {
        $platformName = $this->resolvePlatformName($adapterOrPlatform);

        if (isset($this->decorators[$platformName])) {
            foreach ($this->decorators[$platformName] as $type => $decorator) {
                if ($subject instanceof $type && is_a($decorator, $type, true)) {
                    $decorator->setSubject($subject);
                    return $decorator;
                }
            }
        }

        return $subject;
    }

    /**
     * @return array|PlatformDecoratorInterface[]
     */
    public function getDecorators()
    {
        $platformName = $this->resolvePlatformName($this->getDefaultPlatform());
        return $this->decorators[$platformName];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        if (! $this->subject instanceof PreparableSqlObjectInterface) {
            throw new Exception\RuntimeException('The subject does not appear to implement Zend\Db\Sql\PreparableSqlObjectInterface, thus calling prepareStatement() has no effect');
        }

        $this->getTypeDecorator($this->subject, $adapter)->prepareStatement($adapter, $statementContainer);

        return $statementContainer;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null)
    {
        if (! $this->subject instanceof SqlObjectInterface) {
            throw new Exception\RuntimeException('The subject does not appear to implement Zend\Db\Sql\SqlObjectInterface, thus calling prepareStatement() has no effect');
        }

        $adapterPlatform = $this->resolvePlatform($adapterPlatform);

        return $this->getTypeDecorator($this->subject, $adapterPlatform)->getSqlString($adapterPlatform);
    }

    protected function resolvePlatformName($adapterOrPlatform)
    {
        $platformName = $this->resolvePlatform($adapterOrPlatform)->getName();
        return str_replace([' ', '_'], '', strtolower($platformName));
    }
    /**
     * @param null|PlatformInterface|AdapterInterface $adapterOrPlatform
     *
     * @return PlatformInterface
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function resolvePlatform($adapterOrPlatform)
    {
        if (! $adapterOrPlatform) {
            return $this->getDefaultPlatform();
        }

        if ($adapterOrPlatform instanceof AdapterInterface) {
            return $adapterOrPlatform->getPlatform();
        }

        if ($adapterOrPlatform instanceof PlatformInterface) {
            return $adapterOrPlatform;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            '$adapterOrPlatform should be null, %s, or %s',
            'Zend\Db\Adapter\AdapterInterface',
            'Zend\Db\Adapter\Platform\PlatformInterface'
        ));
    }

    /**
     * @return PlatformInterface
     *
     * @throws Exception\RuntimeException
     */
    protected function getDefaultPlatform()
    {
        if (! $this->defaultPlatform) {
            throw new Exception\RuntimeException('$this->defaultPlatform was not set');
        }

        return $this->defaultPlatform;
    }
}
