<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Zend\Db\Adapter;

class Context
{
    protected $adapter;
    protected $platform;
    protected $driver;
    protected $parameterContainer;

    protected $prefixCounter = [];
    protected $prefixCurrent = [[
        'prefix'      => '',
        'suffixIndex' => 0,
    ]];

    public function __construct(Adapter\Adapter $adapter, Adapter\ParameterContainer $parameterContainer = null)
    {
        $this->adapter            = $adapter;
        $this->platform           = $adapter->getPlatform();
        $this->driver             = $adapter->getDriver();
        $this->parameterContainer = $parameterContainer;
    }

    /**
     * @return Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return Adapter\Platform\PlatformInterface
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @return Adapter\Driver\DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return Adapter\ParameterContainer
     */
    public function getParameterContainer()
    {
        return $this->parameterContainer;
    }

    /**
     * @param string $name
     */
    public function startPrefix($name)
    {
        if (!isset($this->prefixCounter[$name])) {
            $this->prefixCounter[$name] = 0;
        }
        $this->prefixCurrent[] = [
            'prefix'      => $name . ++$this->prefixCounter[$name],
            'suffixIndex' => 0,
        ];
    }

    public function endPrefix()
    {
        array_pop($this->prefixCurrent);
    }

    /**
     * @param string $suffix
     * @return string
     */
    public function getNestedAlias($suffix)
    {
        $curr = &$this->prefixCurrent[count($this->prefixCurrent) - 1];
        return $curr['prefix'] . $suffix . ++$curr['suffixIndex'];
    }
}
