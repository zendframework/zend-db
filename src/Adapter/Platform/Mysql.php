<?php
/**
 * @see       http://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Db\Adapter\Platform;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\Mysqli;
use Zend\Db\Adapter\Driver\Pdo;
use Zend\Db\Adapter\Exception;

class Mysql extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifier = ['`', '`'];

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '``';

    /**
     * @var \mysqli|\PDO
     */
    protected $resource = null;

    /**
     * @param null|\Zend\Db\Adapter\Driver\Mysqli\Mysqli|\Zend\Db\Adapter\Driver\Pdo\Pdo|\mysqli|\PDO $driver
     */
    public function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param \Zend\Db\Adapter\Driver\Mysqli\Mysqli|\Zend\Db\Adapter\Driver\Pdo\Pdo|\mysqli|\PDO $driver
     * @return self Provides a fluent interface
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     */
    public function setDriver($driver)
    {
        // handle Zend\Db drivers
        if ($driver instanceof Mysqli\Mysqli
            || ($driver instanceof Pdo\Pdo && $driver->getDatabasePlatformName() == 'Mysql')
            || ($driver instanceof \mysqli)
            || ($driver instanceof \PDO && $driver->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql')
        ) {
            $this->resource = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException('$driver must be a Mysqli or Mysql PDO Zend\Db\Adapter\Driver, Mysqli instance or MySQL PDO instance');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'MySQL';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        if ($this->resource instanceof DriverInterface) {
            $this->resource = $this->resource->getConnection()->getResource();
        }
        if ($this->resource instanceof \mysqli) {
            return '\'' . $this->resource->real_escape_string($value) . '\'';
        }
        if ($this->resource instanceof \PDO) {
            return $this->resource->quote($value);
        }
        return parent::quoteValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        if ($this->resource instanceof DriverInterface) {
            $this->resource = $this->resource->getConnection()->getResource();
        }
        if ($this->resource instanceof \mysqli) {
            return '\'' . $this->resource->real_escape_string($value) . '\'';
        }
        if ($this->resource instanceof \PDO) {
            return $this->resource->quote($value);
        }
        return parent::quoteTrustedValue($value);
    }
}
