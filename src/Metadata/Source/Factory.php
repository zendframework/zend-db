<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Exception\InvalidArgumentException;
use Zend\Db\Metadata\MetadataInterface;

/**
 * Source metadata factory.
 */
class Factory
{
    /**
     * Create source from adapter
     *
     * @param  Adapter $adapter
     * @return MetadataInterface
     * @throws InvalidArgumentException If adapter platform name not recognized.
     */
    public static function createSourceFromAdapter(Adapter $adapter)
    {
        $platformName = $adapter->getPlatform()->getName();

        switch ($platformName) {
            case 'MySQL':
                return new MysqlMetadata($adapter);
            case 'SQLServer':
                return new SqlServerMetadata($adapter);
            case 'SQLite':
                return new SqliteMetadata($adapter);
            case 'PostgreSQL':
                return new PostgresqlMetadata($adapter);
            case 'Oracle':
                return new OracleMetadata($adapter);
            default:
                throw new InvalidArgumentException("Unknown adapter platform '{$platformName}'");
        }
    }
}
