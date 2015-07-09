<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class DeleteBuilder extends AbstractSqlBuilder
{
    const SPECIFICATION_DELETE = 'delete';
    const SPECIFICATION_WHERE = 'where';

    protected $specifications = [
        self::SPECIFICATION_DELETE => 'DELETE FROM %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
    ];

    /**
     * @param Delete $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Delete(Delete $sqlObject, Context $context)
    {
        return sprintf(
            $this->specifications[self::SPECIFICATION_DELETE],
            $this->resolveTable($sqlObject->table, $context)
        );
    }

    /**
     * @param Delete $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Where(Delete $sqlObject, Context $context)
    {
        $WHERE = $sqlObject->where;
        if ($WHERE->count() == 0) {
            return;
        }

        return sprintf(
            $this->specifications[self::SPECIFICATION_WHERE],
            $this->buildSqlString($WHERE, $context)
        );
    }
}
