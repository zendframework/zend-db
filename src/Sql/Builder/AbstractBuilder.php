<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Zend\Db\Sql\TableSource;
use Zend\Db\Sql\TableIdentifier;

abstract class AbstractBuilder
{
    protected $implodeGlueKey = 'implode_glue';

    /**
     * @param TableIdentifier|string|array $identifier
     * @param Context $context
     * @return array
     */
    protected function nornalizeTable($identifier, Context $context)
    {
        $schema      = null;
        $name        = null;
        $alias       = null;
        $columnAlias = null;

        if ($identifier instanceof TableSource) {
            $alias  = $identifier->getAlias();
            $identifier = $identifier->getSource();
        }
        if ($identifier instanceof TableIdentifier) {
            $name   = $identifier->getTable();
            $schema = $identifier->getSchema();
        } else {
            $name   = $identifier;
        }

        if ($alias) {
            $alias       = $context->getPlatform()->quoteIdentifier($alias);
            $columnAlias = $alias;
        }

        if (is_string($name)) {
            $name = $schema
                        ? $context->getPlatform()->quoteIdentifierInFragment($schema . '.' . $name)
                        : $context->getPlatform()->quoteIdentifier($name);
            if (!$columnAlias) {
                $columnAlias = $name;
            }
        }

        return [
            'name'        => $name,
            'alias'       => $alias,
            'columnAlias' => $columnAlias,
        ];
    }

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
