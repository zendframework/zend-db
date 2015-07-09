<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

abstract class AbstractSqlObject implements SqlObjectInterface
{
    protected $__getProperties = [];

    public function __construct()
    {
        $this->__getProperties = array_flip($this->__getProperties);
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->__getProperties)) {
            throw new Exception\InvalidArgumentException(
                'Not a valid property "'. $name . '" for this object'
            );
        }
        return $this->{$name};
    }
}
