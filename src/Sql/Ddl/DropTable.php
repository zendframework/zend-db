<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\AbstractSql;
use Zend\Db\Sql\TableIdentifier;

class DropTable extends AbstractSql implements SqlInterface
{
    public const TABLE = 'table';

    /** @var array */
    protected $specifications = [
        self::TABLE => 'DROP TABLE %1$s',
    ];

    /** @var string */
    protected $table = '';

    /**
     * @param string|TableIdentifier $table
     */
    public function __construct($table = '')
    {
        $this->table = $table;
    }

    protected function processTable(?PlatformInterface $adapterPlatform = null) : array
    {
        return [$this->resolveTable($this->table, $adapterPlatform)];
    }
}
