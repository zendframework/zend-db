<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Predicate;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class NotBetweenBuilder extends AbstractSqlBuilder
{
    protected $specification = '%1$s NOT BETWEEN %2$s AND %3$s';

    /**
     * @param \Zend\Db\Sql\Predicate\NotBetween $expression
     * @param Context $context
     * @return array
     */
    public function build($expression, Context $context)
    {
        $this->validateSqlObject($expression, 'Zend\Db\Sql\Predicate\NotBetween', __METHOD__);
        return [[
            'spec' => $this->specification,
            'params' => [
                $expression->getIdentifier(),
                $expression->getMinValue(),
                $expression->getMaxValue(),
            ],
        ]];
    }
}