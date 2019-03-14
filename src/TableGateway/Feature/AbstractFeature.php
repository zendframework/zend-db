<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\Exception;

abstract class AbstractFeature extends AbstractTableGateway
{
    /** @var AbstractTableGateway */
    protected $tableGateway;

    protected $sharedData = [];

    public function getName()
    {
        return get_class($this);
    }

    public function setTableGateway(AbstractTableGateway $tableGateway) : void
    {
        $this->tableGateway = $tableGateway;
    }

    public function initialize() : void
    {
        throw new Exception\RuntimeException('This method is not intended to be called on this object.');
    }

    public function getMagicMethodSpecifications() : array
    {
        return [];
    }


    /*
    public function preInitialize();
    public function postInitialize();
    public function preSelect(Select $select);
    public function postSelect(StatementInterface $statement, ResultInterface $result, ResultSetInterface $resultSet);
    public function preInsert(Insert $insert);
    public function postInsert(StatementInterface $statement, ResultInterface $result);
    public function preUpdate(Update $update);
    public function postUpdate(StatementInterface $statement, ResultInterface $result);
    public function preDelete(Delete $delete);
    public function postDelete(StatementInterface $statement, ResultInterface $result);
    */
}
