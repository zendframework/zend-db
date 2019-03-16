<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Platform\IbmDb2;

use Zend\Db\Sql\Platform\AbstractPlatform;
use Zend\Db\Sql\Select;

class IbmDb2 extends AbstractPlatform
{
    /**
     * @param SelectDecorator $selectDecorator
     */
    public function __construct(SelectDecorator $selectDecorator = null)
    {
        $this->setTypeDecorator(Select::class, $selectDecorator ?: new SelectDecorator());
    }
}
