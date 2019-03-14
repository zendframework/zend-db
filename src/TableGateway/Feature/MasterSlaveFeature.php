<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;

class MasterSlaveFeature extends AbstractFeature
{
    /**
     * @var AdapterInterface
     */
    protected $slaveAdapter = null;

    /**
     * @var Sql
     */
    protected $masterSql = null;

    /**
     * @var Sql
     */
    protected $slaveSql = null;

    /**
     * Constructor
     *
     * @param AdapterInterface $slaveAdapter
     * @param Sql|null $slaveSql
     */
    public function __construct(AdapterInterface $slaveAdapter, Sql $slaveSql = null)
    {
        $this->slaveAdapter = $slaveAdapter;
        if ($slaveSql) {
            $this->slaveSql = $slaveSql;
        }
    }

    public function getSlaveAdapter() : AdapterInterface
    {
        return $this->slaveAdapter;
    }

    /**
     * @return Sql
     */
    public function getSlaveSql() : Sql
    {
        return $this->slaveSql;
    }

    /**
     * after initialization, retrieve the original adapter as "master"
     */
    public function postInitialize() : void
    {
        $this->masterSql = $this->tableGateway->sql;
        if ($this->slaveSql === null) {
            $this->slaveSql = new Sql(
                $this->slaveAdapter,
                $this->tableGateway->sql->getTable(),
                $this->tableGateway->sql->getSqlPlatform()
            );
        }
    }

    /**
     * preSelect()
     * Replace adapter with slave temporarily
     */
    public function preSelect() : void
    {
        $this->tableGateway->sql = $this->slaveSql;
    }

    /**
     * postSelect()
     * Ensure to return to the master adapter
     */
    public function postSelect() : void
    {
        $this->tableGateway->sql = $this->masterSql;
    }
}
