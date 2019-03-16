<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Platform\Mysql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Select;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /** @var Select */
    protected $subject;

    /**
     * @param Select $select
     */
    public function setSubject($select)
    {
        $this->subject = $select;
    }

    protected function localizeVariables() : void
    {
        parent::localizeVariables();
        if ($this->limit === null && $this->offset !== null) {
            $this->specifications[self::LIMIT] = 'LIMIT 18446744073709551615';
        }
    }

    protected function processLimit(
        PlatformInterface  $platform,
        DriverInterface    $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->limit === null && $this->offset !== null) {
            return [''];
        }

        if ($this->limit === null) {
            return;
        }

        if ($parameterContainer) {
            $paramPrefix = $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'limit', $this->limit, ParameterContainer::TYPE_INTEGER);

            return [$driver->formatParameterName($paramPrefix . 'limit')];
        }

        return [$this->limit];
    }

    protected function processOffset(
        PlatformInterface  $platform,
        DriverInterface    $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->offset === null) {
            return;
        }
        if ($parameterContainer) {
            $paramPrefix = $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName($paramPrefix . 'offset')];
        }

        return [$this->offset];
    }
}
