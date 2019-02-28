<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\TableGateway;

use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Where;

interface TableGatewayInterface
{
    /**
     * Return the table name
     *
     * @return string
     */
    public function getTable();

    /**
     * Select values by conditions
     *
     * @param  Where|\Closure|string|array|null $where
     * @return ResultSetInterface
     */
    public function select($where = null);

    /**
     * Insert values given by array
     *
     * @param  array $set
     * @return int number of affected rows
     */
    public function insert($set);

    /**
     * Update values by condition
     *
     * @param  array $set
     * @param  string|array|\Closure|null $where
     * @return int number of affected rows
     */
    public function update($set, $where = null);

    /**
     * Delete values by condition
     *
     * @param  Where|\Closure|string|array $where
     * @return int number of affected rows
     */
    public function delete($where);
}
