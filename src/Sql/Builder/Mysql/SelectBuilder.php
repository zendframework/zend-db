<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\Mysql;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter;
use Zend\Db\Sql\Builder\sql92\SelectBuilder as BaseBuilder;
use Zend\Db\Sql\Builder\Context;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\ExpressionInterface;

class SelectBuilder extends BaseBuilder
{
    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return null|array
     */
    protected function build_Limit(Select $sqlObject, Context $context)
    {
        $limit = $limitParam = $sqlObject->limit;
        if ($limit === null && $sqlObject->offset !== null) {
            $limitParam = '18446744073709551615';
        } elseif ($limit === null) {
            return;
        } else {
            $limitParam = new ExpressionParameter($limit);
            $limitParam
                    ->setType(ExpressionInterface::TYPE_VALUE)
                    ->setName('limit')
                    ->setOptions([
                        'errata'   => Adapter\ParameterContainer::TYPE_INTEGER,
                        'isQuoted' => true,
                    ]);
        }

        return [
            'spec' => $this->limitSpecification,
            'params' => [
                $limitParam,
            ],
        ];
    }

    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return null|array
     */
    protected function build_Offset(Select $sqlObject, Context $context)
    {
        $offset = $sqlObject->offset;
        if ($offset === null) {
            return;
        }
        $offset = new ExpressionParameter($offset, ExpressionInterface::TYPE_VALUE, 'offset');
        $offset->setOptions([
            'errata'   => Adapter\ParameterContainer::TYPE_INTEGER,
            'isQuoted' => true,
        ]);
        return [
            'spec' => $this->offsetSpecification,
            'params' => [
                $offset,
            ],
        ];
    }
}
