<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\StatementInterface;

class Sql
{
    /** @var AdapterInterface */
    protected $adapter = null;

    /** @var TableIdentifier */
    protected $table = null;

    /** @var Builder\Builder */
    protected $builder = null;

    /**
     * @param null|AdapterInterface                  $adapter
     * @param null|string|array|TableIdentifier $table
     * @param null|Builder\Builder              $platformBuilder
     */
    public function __construct(AdapterInterface $adapter = null, $table = null, Builder\Builder $builder = null)
    {
        $this->adapter = $adapter;
        if ($table) {
            $this->setTable($table);
        }
        $this->builder = $builder ?: new Builder\Builder($adapter);
    }

    /**
     * @return null|\Zend\Db\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return bool
     */
    public function hasTable()
    {
        return ($this->table !== null);
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setTable($table)
    {
        if (!$table) {
            throw new Exception\InvalidArgumentException('Table must be a string, array or instance of TableIdentifier.');
        }
        $this->table = TableIdentifier::factory($table);
        return $this;
    }

    /**
     * @return TableIdentifier
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return Builder\Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Select
     * @throws Exception\InvalidArgumentException
     */
    public function select($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table->getTable()
            ));
        }
        return new Select(($table) ?: $this->table);
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Insert
     * @throws Exception\InvalidArgumentException
     */
    public function insert($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table->getTable()
            ));
        }
        return new Insert(($table) ?: $this->table);
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Update
     * @throws Exception\InvalidArgumentException
     */
    public function update($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table->getTable()
            ));
        }
        return new Update(($table) ?: $this->table);
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Delete
     * @throws Exception\InvalidArgumentException
     */
    public function delete($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table->getTable()
            ));
        }
        return new Delete(($table) ?: $this->table);
    }

    /**
     * @param PreparableSqlObjectInterface $sqlObject
     * @param null|AdapterInterface       $adapter
     *
     * @return StatementInterface
     */
    public function prepareStatementForSqlObject(PreparableSqlObjectInterface $sqlObject, AdapterInterface $adapter = null)
    {
        return $this->builder->prepareStatement(
            $sqlObject,
            $adapter ?: $this->adapter
        );
    }

    /**
     * @param SqlObjectInterface     $sqlObject
     * @param null|AdapterInterface $adapter
     *
     * @return string
     *
     * @throws Exception\InvalidArgumentException
     */
    public function buildSqlString(SqlObjectInterface $sqlObject, AdapterInterface $adapter = null)
    {
        return $this->builder->getSqlString(
            $sqlObject,
            $adapter ?: $this->adapter
        );
    }
}
