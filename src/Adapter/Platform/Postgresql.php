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
use Zend\Db\Adapter\Driver\Pgsql;
use Zend\Db\Adapter\Exception;

class Postgresql extends AbstractPlatform
{
    /**
     * Overrides value from AbstractPlatform to use proper escaping for Postgres
     *
     * @var string
     */
    protected $quoteIdentifierTo = '""';

    /**
     * @var resource
     */
    protected $driver = null;

    /**
     * @param null|\Zend\Db\Adapter\Driver\Pgsql\Pgsql|\Zend\Db\Adapter\Driver\Pdo\Pdo|resource $driver
     */
    public function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param \Zend\Db\Adapter\Driver\Pgsql\Pgsql|\Zend\Db\Adapter\Driver\Pdo\Pdo|resource $driver
     * @return self Provides a fluent interface
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     */
    public function setDriver($driver)
    {
        if ($driver instanceof Pgsql\Pgsql
            || ($driver instanceof Pdo\Pdo && $driver->getDatabasePlatformName() == 'Postgresql')
            || (is_resource($driver) && (in_array(get_resource_type($driver), ['pgsql link', 'pgsql link persistent'])))
        ) {
            $this->driver = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Pgsql, Postgresql PDO Zend\Db\Adapter\Driver or pgsql link resource'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'PostgreSQL';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierChain($identifierChain)
    {
        return '"' . implode('"."', (array) str_replace('"', '""', $identifierChain)) . '"';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        $quotedViaResource = $this->quoteViaResource($value);

        return $quotedViaResource !== null ? $quotedViaResource : ('E' . parent::quoteValue($value));
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        $quotedViaResource = $this->quoteViaResource($value);

        return $quotedViaResource !== null ? $quotedViaResource : ('E' . parent::quoteTrustedValue($value));
    }

    /**
     * @param string $value
     *
     * @return null|string
     */
    protected function quoteViaResource($value)
    {
        if ($this->driver instanceof DriverInterface) {
            $resource = $this->driver->getConnection()->getResource();
        } else {
            $resource = $this->driver;
        }

        if (is_resource($resource)) {
            return '\'' . pg_escape_string($resource, $value) . '\'';
        }
        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        return null;
    }
}
