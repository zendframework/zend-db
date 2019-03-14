<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\RowGateway;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class RowGateway extends AbstractRowGateway
{
    /**
     * Constructor
     *
     * @param string $primaryKeyColumn
     * @param string|\Zend\Db\Sql\TableIdentifier $table
     * @param null|Adapter|Sql $adapterOrSql
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(string $primaryKeyColumn, $table, $adapterOrSql = null)
    {
        $this->primaryKeyColumn = $primaryKeyColumn === '' ? [] : (array) $primaryKeyColumn;

        $this->table = $table;

        if ($adapterOrSql instanceof Sql) {
            $this->sql = $adapterOrSql;
        } elseif ($adapterOrSql instanceof Adapter) {
            $this->sql = new Sql($adapterOrSql, $this->table);
        } else {
            throw new Exception\InvalidArgumentException('A valid Sql object was not provided.');
        }

        if ($this->sql->getTable() !== $this->table) {
            throw new Exception\InvalidArgumentException(
                'The Sql object provided does not have a table that matches this row object'
            );
        }

        $this->initialize();
    }
}
