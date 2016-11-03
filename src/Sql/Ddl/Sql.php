<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Sql as BaseSql;
use Zend\Db\Sql\Exception;

class Sql extends BaseSql\AbstractSql
{
    /**
     * @param null|string|array|BaseSql\TableIdentifier $table
     * @return AlterTable
     * @throws Exception\InvalidArgumentException
     */
    public function alterTable($table = null)
    {
        $this->validateTable($table);
        return new AlterTable($table ?: $this->table);
    }

    /**
     * @param null|string|array|BaseSql\TableIdentifier $table
     * @return CreateTable
     * @throws Exception\InvalidArgumentException
     */
    public function createTable($table = null)
    {
        $this->validateTable($table);
        return new CreateTable($table ?: $this->table);
    }

    /**
     * @param null|string|array|BaseSql\TableIdentifier $table
     * @return DropTable
     * @throws Exception\InvalidArgumentException
     */
    public function dropTable($table = null)
    {
        $this->validateTable($table);
        return new DropTable($table ?: $this->table);
    }
}
