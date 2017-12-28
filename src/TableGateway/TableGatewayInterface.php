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
     * @return string
     */
    public function getTable();

    /**
     * Select
     *
     * @param  Where|\Closure|string|array $where
     * @return ResultSetInterface
     */
    public function select($where = null);

    /**
     * Insert
     *
     * @param  array $set
     * @return int
     */
    public function insert($set);

    /**
     * Update
     *
     * @param  array $set
     * @param  string|array|\Closure $where
     *
     * @return int
     */
    public function update($set, $where = null);

    /**
     * Delete
     *
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    public function delete($where);
}
