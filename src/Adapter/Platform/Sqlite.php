<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter\Platform;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\Pdo;
use Zend\Db\Adapter\Exception;

class Sqlite extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifier = ['"','"'];

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '\'';

    /**
     * @var Pdo\Pdo
     */
    protected $driver = null;

    /**
     * @param null|\Zend\Db\Adapter\Driver\Pdo\Pdo
     */
    public function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param \Zend\Db\Adapter\Driver\Pdo\Pdo $driver
     * @return self Provides a fluent interface
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     */
    public function setDriver($driver)
    {
        if ($driver instanceof Pdo\Pdo && $driver->getDatabasePlatformName() == 'Sqlite') {
            $this->driver = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Sqlite PDO Zend\Db\Adapter\Driver'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'SQLite';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        $resource = $this->driver;

        if ($resource instanceof DriverInterface) {
            $resource = $resource->getConnection()->getResource();
        }

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        return parent::quoteValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        $resource = $this->driver;

        if ($resource instanceof DriverInterface) {
            $resource = $resource->getConnection()->getResource();
        }

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        return parent::quoteTrustedValue($value);
    }
}
