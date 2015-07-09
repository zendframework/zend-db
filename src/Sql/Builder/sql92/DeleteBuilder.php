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
    protected $deleteSpecification ='DELETE FROM %1$s';
    protected $whereSpecification = 'WHERE %1$s';

    /**
     * @param Delete $sqlObject
     * @param Context $context
     * @return array
     */
    public function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Delete', __METHOD__);
        return [
            $this->build_Delete($sqlObject, $context),
            $this->build_Where($sqlObject, $context),
        ];
    }

    /**
     * @param Delete $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Delete(Delete $sqlObject, Context $context)
    {
        return [
            'spec' => $this->deleteSpecification,
            'params' => [
                $this->nornalizeTable($sqlObject->table, $context)['name'],
            ],
        ];
    }

    /**
     * @param Delete $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Where(Delete $sqlObject, Context $context)
    {
        if ($sqlObject->where->count() == 0) {
            return;
        }
        return [
            'spec' => $this->whereSpecification,
            'params' => $sqlObject->where
        ];
    }
}
