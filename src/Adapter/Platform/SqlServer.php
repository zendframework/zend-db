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

class SqlServer extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifier = ['[',']'];

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '\\';

    /**
     * @var resource
     */
    protected $driver = null;

    /**
     * @param null|\Zend\Db\Adapter\Driver\Sqlsrv\Sqlsrv|\Zend\Db\Adapter\Driver\Pdo\Pdo|resource $driver
     */
    public function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param \Zend\Db\Adapter\Driver\Sqlsrv\Sqlsrv|\Zend\Db\Adapter\Driver\Pdo\Pdo|resource $driver
     * @return self Provides a fluent interface
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     */
    public function setDriver($driver)
    {
        // handle Zend\Db drivers
        if ($driver instanceof Pdo\Pdo && in_array($driver->getDatabasePlatformName(), ['SqlServer', 'Dblib'])) {
            $this->driver = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Sqlsrv PDO Zend\Db\Adapter\Driver'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'SQLServer';
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteIdentifierSymbol()
    {
        return $this->quoteIdentifier;
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierChain($identifierChain)
    {
        return '[' . implode('].[', (array) $identifierChain) . ']';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        if ($this->driver instanceof DriverInterface) {
            $resource = $this->driver->getConnection()->getResource();
        } else {
            $resource = $this->driver;
        }

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }
        trigger_error(
            'Attempting to quote a value in ' . __CLASS__ . ' without extension/driver support '
                . 'can introduce security vulnerabilities in a production environment.'
        );

        return '\'' . str_replace('\'', '\'\'', addcslashes($value, "\000\032")) . '\'';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        if ($this->driver instanceof DriverInterface) {
            $resource = $this->driver->getConnection()->getResource();
        } else {
            $resource = $this->driver;
        }

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }
        return '\'' . str_replace('\'', '\'\'', $value) . '\'';
    }
}
