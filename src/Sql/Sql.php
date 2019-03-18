<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\AbstractPlatform;
use function is_array;
use function is_string;


class Sql
{
    /** @var AdapterInterface */
    protected $adapter;

    /** @var string|array|TableIdentifier */
    protected $table;

    /** @var Platform\Platform */
    protected $sqlPlatform;

    /**
     * @param AdapterInterface                  $adapter
     * @param null|string|array|TableIdentifier $table
     * @param null|AbstractPlatform             $sqlPlatform @deprecated since version 3.0
     */
    public function __construct(
        AdapterInterface           $adapter,
        $table = null,
        ?AbstractPlatform $sqlPlatform = null
    ) {
        $this->adapter = $adapter;
        if ($table) {
            $this->setTable($table);
        }
        $this->sqlPlatform = $sqlPlatform ?? new Platform\Platform($adapter);
    }

    public function getAdapter() : ?AdapterInterface
    {
        return $this->adapter;
    }

    public function hasTable() : bool
    {
        return ($this->table !== null);
    }

    /**
     * @param string|array|TableIdentifier $table
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setTable($table) : self
    {
        if (is_string($table) || is_array($table) || $table instanceof TableIdentifier) {
            $this->table = $table;
        } else {
            throw new Exception\InvalidArgumentException(
                'Table must be a string, array or instance of TableIdentifier.'
            );
        }
        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getSqlPlatform() : Platform\Platform
    {
        return $this->sqlPlatform;
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Select
     */
    public function select($table = null) : Select
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Select($table ?? $this->table);
    }

    /**
     * @param null|string|TableIdentifier $table
     * @return Insert
     */
    public function insert($table = null) : Insert
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Insert($table ?? $this->table);
    }

    /**
     * @param null|string|TableIdentifier $table
     * @return Update
     */
    public function update($table = null) : Update
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Update($table ?? $this->table);
    }

    /**
     * @param null|string|TableIdentifier $table
     * @return Delete
     */
    public function delete($table = null) : Delete
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Delete($table ?? $this->table);
    }

    public function prepareStatementForSqlObject(
        PreparableSqlInterface $sqlObject,
        ?StatementInterface    $statement = null,
        ?AdapterInterface      $adapter = null
    ) : StatementInterface {
        $adapter   = $adapter ?? $this->adapter;
        $statement = $statement ?? $adapter->getDriver()->createStatement();

        return $this->sqlPlatform->setSubject($sqlObject)->prepareStatement($adapter, $statement);
    }

    /**
     * Get sql string using platform or sql object
     *
     * @param SqlInterface           $sqlObject
     * @param PlatformInterface|null $platform
     * @return string
     *
     * @deprecated Deprecated in 2.4. Use buildSqlString() instead
     */
    public function getSqlStringForSqlObject(SqlInterface $sqlObject, ?PlatformInterface $platform = null) : string
    {
        $platform = $platform ?? $this->adapter->getPlatform();
        return $this->sqlPlatform->setSubject($sqlObject)->getSqlString($platform);
    }

    public function buildSqlString(SqlInterface $sqlObject, ?AdapterInterface $adapter = null) : string
    {
        return $this
            ->sqlPlatform
            ->setSubject($sqlObject)
            ->getSqlString($adapter ? $adapter->getPlatform() : $this->adapter->getPlatform());
    }
}
