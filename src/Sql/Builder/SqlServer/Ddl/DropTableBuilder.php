<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\SqlServer\Ddl;

use Zend\Db\Sql\Builder\sql92\Ddl\DropTableBuilder as BaseDropTableBuilder;
use Zend\Db\Sql\Builder\Context;
use Zend\Db\Sql\Ddl\DropTable;

class DropTableBuilder extends BaseDropTableBuilder
{
    protected $ifExistsSpecification = 'IF OBJECT_ID(\'%1$s\', \'U\') IS NOT NULL DROP TABLE %2$s;';

    /**
     * @param DropTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Table(DropTable $sqlObject, Context $context)
    {
        if ($sqlObject->ifExists) {
            $tableString =
                  $sqlObject->table->getSchema()
                        ? $sqlObject->table->getSchema() . $context->getPlatform()->getIdentifierSeparator()
                        : ''
                . $sqlObject->table->getTable();

            return [
                'spec'   => $this->ifExistsSpecification,
                'params' => [
                    $tableString,
                    $sqlObject->table,
                ],
            ];
        } else {
            return [
                'spec'   => $this->tableSpecification,
                'params' => $sqlObject->table,
            ];
        }
    }
}
