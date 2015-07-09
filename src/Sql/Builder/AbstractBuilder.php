<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

abstract class AbstractBuilder
{
    abstract protected function buildSqlString($sqlObject, Context $context);

    /**
     *
     * @param mixed $argument
     * @param string $class
     * @param string $method
     * @return null
     * @throws \Zend\Db\Sql\Exception\InvalidArgumentException
     */
    protected function validateSqlObject($argument, $class, $method)
    {
        if ($argument instanceof $class) {
            return;
        }
        throw new \Zend\Db\Sql\Exception\InvalidArgumentException(sprintf(
            'Argument 1 passed to %s must be an instance of %s, instance of %s given',
            $method . '()',
            $class,
            is_object($argument) ? get_class($argument) : gettype($argument)
        ));
    }
}
