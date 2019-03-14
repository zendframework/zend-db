<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;

class GlobalAdapterFeature extends AbstractFeature
{
    /** @var Adapter[] */
    protected static $staticAdapters = [];

    public static function setStaticAdapter(Adapter $adapter) : void
    {
        $class = static::class;

        static::$staticAdapters[$class] = $adapter;
        if ($class === __CLASS__) {
            static::$staticAdapters[__CLASS__] = $adapter;
        }
    }

    public static function getStaticAdapter() : Adapter
    {
        $class = static::class;

        // class specific adapter
        if (isset(static::$staticAdapters[$class])) {
            return static::$staticAdapters[$class];
        }

        // default adapter
        if (isset(static::$staticAdapters[__CLASS__])) {
            return static::$staticAdapters[__CLASS__];
        }

        throw new Exception\RuntimeException('No database adapter was found in the static registry.');
    }

    /**
     * after initialization, retrieve the original adapter as "master"
     */
    public function preInitialize() : void
    {
        $this->tableGateway->adapter = self::getStaticAdapter();
    }
}
