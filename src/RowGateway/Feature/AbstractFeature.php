<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\RowGateway\Feature;

use Zend\Db\RowGateway\AbstractRowGateway;
use Zend\Db\RowGateway\Exception;

abstract class AbstractFeature extends AbstractRowGateway
{
    /** @var AbstractRowGateway */
    protected $rowGateway;

    /** @var array */
    protected $sharedData = [];

    /**
     * @return string|bool
     */
    public function getName()
    {
        return get_class($this);
    }

    public function setRowGateway(AbstractRowGateway $rowGateway) : void
    {
        $this->rowGateway = $rowGateway;
    }

    public function initialize()
    {
        throw new Exception\RuntimeException('This method is not intended to be called on this object.');
    }

    public function getMagicMethodSpecifications() : array
    {
        return [];
    }
}
