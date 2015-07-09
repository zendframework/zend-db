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

class SelectBuilder extends BaseBuilder
{
    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return null|array
     */
    protected function build_Limit(Select $sqlObject, Context $context)
    {
        $limit = $sqlObject->limit;
        if ($limit === null) {
            return $sqlObject->offset === null
                    ? null
                    : ['18446744073709551615'];
        }

        if ($context->getParameterContainer()) {
            $context->getParameterContainer()->offsetSet('limit', $limit, Adapter\ParameterContainer::TYPE_INTEGER);
            $limit = $context->getDriver()->formatParameterName('limit');
        }

        return [$limit];
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
        if ($context->getParameterContainer()) {
            $context->getParameterContainer()->offsetSet('offset', $offset, Adapter\ParameterContainer::TYPE_INTEGER);
            $offset = $context->getDriver()->formatParameterName('offset');
        }

        return [$offset];
    }
}
