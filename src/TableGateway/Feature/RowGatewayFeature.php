<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\RowGateway\RowGateway;
use Zend\Db\RowGateway\RowGatewayInterface;
use Zend\Db\TableGateway\Exception;
use Zend\Db\TableGateway\Feature\MetadataFeature;

class RowGatewayFeature extends AbstractFeature
{
    /**
     * @var array
     */
    protected $constructorArguments = [];

    public function __construct()
    {
        $this->constructorArguments = func_get_args();
    }

    public function postInitialize()
    {
        $args = $this->constructorArguments;

        /** @var $resultSetPrototype ResultSet */
        $resultSetPrototype = $this->tableGateway->resultSetPrototype;

        if (! $this->tableGateway->resultSetPrototype instanceof ResultSet) {
            throw new Exception\RuntimeException(
                'This feature ' . __CLASS__ . ' expects the ResultSet to be an instance of Zend\Db\ResultSet\ResultSet'
            );
        }

        if (isset($args[0])) {
            if (is_string($args[0])) {
                $primaryKey = $args[0];
                $rowGatewayPrototype = new RowGateway(
                    $primaryKey,
                    $this->tableGateway->table,
                    $this->tableGateway->adapter
                );
                $resultSetPrototype->setArrayObjectPrototype($rowGatewayPrototype);
            } elseif ($args[0] instanceof RowGatewayInterface) {
                $rowGatewayPrototype = $args[0];
                $resultSetPrototype->setArrayObjectPrototype($rowGatewayPrototype);
            }
        } else {
            // get from metadata feature
            $metadata = $this->tableGateway->featureSet->getFeatureByClassName(
                MetadataFeature::class
            );
            if ($metadata === false || ! isset($metadata->sharedData['metadata'])) {
                throw new Exception\RuntimeException(
                    'No information was provided to the RowGatewayFeature and/or no MetadataFeature could be consulted '
                    . 'to find the primary key necessary for RowGateway object creation.'
                );
            }
            $primaryKey = $metadata->sharedData['metadata']['primaryKey'];
            $rowGatewayPrototype = new RowGateway(
                $primaryKey,
                $this->tableGateway->table,
                $this->tableGateway->adapter
            );
            $resultSetPrototype->setArrayObjectPrototype($rowGatewayPrototype);
        }
    }
}
