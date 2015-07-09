<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\SqlServer\Ddl;

use Zend\Db\Sql\Builder\sql92\Ddl\CreateTableBuilder as BaseBuilder;
use Zend\Db\Sql\Builder\Context;
use Zend\Db\Sql\Ddl\CreateTable;

class CreateTableBuilder extends BaseBuilder
{
    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Table(CreateTable $sqlObject, Context $context)
    {
        $table = ($sqlObject->isTemporary ? '#' : '') . ltrim($sqlObject->table, '#');
        return [
            '',
            $context->getPlatform()->quoteIdentifier($table),
        ];
    }
}
