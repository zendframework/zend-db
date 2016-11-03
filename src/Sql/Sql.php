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

class Sql extends AbstractSql
{
    /**
     * @var Ddl\Sql
     */
    protected $ddl;

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Select
     * @throws Exception\InvalidArgumentException
     */
    public function select($table = null)
    {
        $this->validateTable($table);
        return new Select(($table) ?: $this->table);
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Insert
     * @throws Exception\InvalidArgumentException
     */
    public function insert($table = null)
    {
        $this->validateTable($table);
        return new Insert(($table) ?: $this->table);
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Update
     * @throws Exception\InvalidArgumentException
     */
    public function update($table = null)
    {
        $this->validateTable($table);
        return new Update(($table) ?: $this->table);
    }

    /**
     * @param null|string|array|TableIdentifier $table
     * @return Delete
     * @throws Exception\InvalidArgumentException
     */
    public function delete($table = null)
    {
        $this->validateTable($table);
        return new Delete(($table) ?: $this->table);
    }

    /**
     * @return Ddl\Sql
     */
    public function getDdl()
    {
        if (!$this->ddl) {
            $this->ddl = new Ddl\Sql($this->adapter, $this->table, $this->builder);
        }
        return $this->ddl;
    }

    /**
     * @param PreparableSqlObjectInterface $sqlObject
     * @param null|AdapterInterface       $adapter
     *
     * @return StatementInterface
     */
    public function prepareSqlStatement(PreparableSqlObjectInterface $sqlObject, AdapterInterface $adapter = null)
    {
        return $this->builder->prepareSqlStatement(
            $sqlObject,
            $adapter ?: $this->adapter
        );
    }
}
