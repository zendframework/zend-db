<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;
use Zend\Db\Sql\Ddl\DropTable;

class DropTableBuilder extends AbstractSqlBuilder
{
    protected $tableSpecification = 'DROP TABLE %1$s';

    /**
     * @param DropTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Ddl\DropTable', __METHOD__);
        return [
            $this->build_Table($sqlObject, $context),
        ];
    }

    /**
     * @param DropTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Table(DropTable $sqlObject, Context $context)
    {
        return [
            'spec' => $this->tableSpecification,
            'params' => [
                $sqlObject->table,
            ],
        ];
    }
}
