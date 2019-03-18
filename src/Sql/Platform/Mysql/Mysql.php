<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Platform\Mysql;

use Zend\Db\Sql\Platform\AbstractPlatform;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Platform\Mysql\Ddl\AlterTableDecorator;
use Zend\Db\Sql\Platform\Mysql\Ddl\CreateTableDecorator;
use Zend\Db\Sql\Select;

class Mysql extends AbstractPlatform
{
    public function __construct()
    {
        $this->setTypeDecorator(Select::class, new SelectDecorator());
        $this->setTypeDecorator(CreateTable::class, new CreateTableDecorator());
        $this->setTypeDecorator(AlterTable::class, new AlterTableDecorator());
    }
}
