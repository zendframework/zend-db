<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\Oracle;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Builder\sql92\SelectBuilder as BaseBuilder;
use Zend\Db\Sql\Builder\Context;

class SelectBuilder extends BaseBuilder
{
    /**
     * @see \Zend\Db\Sql\Select::renderTable
     */
    protected function renderTable($table, $alias = null)
    {
        return $table . ($alias ? ' ' . $alias : '');
    }

    protected function build_Limit(Select $sqlObject, Context $context)
    {
        return;
    }

    protected function build_Offset(Select $sqlObject, Context $context, &$sqls = null, &$parameters = null)
    {
        $LIMIT = $sqlObject->limit;
        $OFFSET = $sqlObject->offset;
        if ($LIMIT === null && $OFFSET === null) {
            return;
        }

        $selectParameters = $parameters[self::SPECIFICATION_SELECT];

        $starSuffix = $context->getPlatform()->getIdentifierSeparator() . Select::SQL_STAR;
        foreach ($selectParameters[0] as $i => $columnParameters) {
            if ($columnParameters[0] == Select::SQL_STAR || (isset($columnParameters[1]) && $columnParameters[1] == Select::SQL_STAR) || strpos($columnParameters[0], $starSuffix)) {
                $selectParameters[0] = [[Select::SQL_STAR]];
                break;
            }
            if (isset($columnParameters[1])) {
                array_shift($columnParameters);
                $selectParameters[0][$i] = $columnParameters;
            }
        }

        if ($OFFSET === null) {
            $OFFSET = 0;
        }

        // first, produce column list without compound names (using the AS portion only)
        array_unshift($sqls, $this->createSqlFromSpecificationAndParameters(
            ['SELECT %1$s FROM (SELECT b.%1$s, rownum b_rownum FROM (' => current($this->specifications[self::SPECIFICATION_SELECT])], $selectParameters
        ));

        if ($context->getParameterContainer()) {
            $parameterContainer = $context->getParameterContainer();
            if ($LIMIT === null) {
                array_push($sqls, ') b ) WHERE b_rownum > (:offset)');
                $parameterContainer->offsetSet('offset', $OFFSET, $parameterContainer::TYPE_INTEGER);
            } else {
                // create bottom part of query, with offset and limit using row_number
                array_push($sqls, ') b WHERE rownum <= (:offset+:limit)) WHERE b_rownum >= (:offset + 1)');
                $parameterContainer->offsetSet('offset', $OFFSET, $parameterContainer::TYPE_INTEGER);
                $parameterContainer->offsetSet('limit', $LIMIT, $parameterContainer::TYPE_INTEGER);
            }
        } else {
            if ($LIMIT === null) {
                array_push($sqls, ') b ) WHERE b_rownum > ('. (int) $OFFSET. ')'
                );
            } else {
                array_push($sqls, ') b WHERE rownum <= ('
                        . (int) $OFFSET
                        . '+'
                        . (int) $LIMIT
                        . ')) WHERE b_rownum >= ('
                        . (int) $OFFSET
                        . ' + 1)'
                );
            }
        }

        $sqls[self::SPECIFICATION_SELECT] = $this->createSqlFromSpecificationAndParameters(
            $this->specifications[self::SPECIFICATION_SELECT],
            $parameters[self::SPECIFICATION_SELECT]
        );
    }
}
