<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\SqlServer;

use Zend\Db\Sql\Builder\sql92\SelectBuilder as BaseBuilder;
use Zend\Db\Sql\Builder\Context;
use Zend\Db\Sql\Select;

class SelectBuilder extends BaseBuilder
{
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

        /** if this is a DISTINCT query then real SELECT part goes to second element in array **/
        $parameterIndex = 0;
        if ($selectParameters[0] === 'DISTINCT') {
            unset($selectParameters[0]);
            $selectParameters = array_values($selectParameters);
            $parameterIndex = 1;
        }

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

        // first, produce column list without compound names (using the AS portion only)
        array_unshift($sqls, $this->createSqlFromSpecificationAndParameters(
            ['SELECT %1$s FROM (' => current($this->specifications[self::SPECIFICATION_SELECT])],
            $selectParameters
        ));

        if ($context->getParameterContainer()) {
            $parameterContainer = $context->getParameterContainer();
            // create bottom part of query, with offset and limit using row_number
            $limitParamName = $context->getDriver()->formatParameterName('limit');
            $offsetParamName = $context->getDriver()->formatParameterName('offset');
            $offsetForSumParamName = $context->getDriver()->formatParameterName('offsetForSum');
            array_push($sqls, ') AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN '
                . $offsetParamName . '+1 AND ' . $limitParamName . '+' . $offsetForSumParamName);
            $parameterContainer->offsetSet('offset', $OFFSET);
            $parameterContainer->offsetSet('limit', $LIMIT);
            $parameterContainer->offsetSetReference('offsetForSum', 'offset');
        } else {
            array_push($sqls, ') AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN '
                . (int) $OFFSET . '+1 AND '
                . (int) $LIMIT . '+' . (int) $OFFSET
            );
        }

        if (isset($sqls[self::SPECIFICATION_ORDER])) {
            $orderBy = $sqls[self::SPECIFICATION_ORDER];
            unset($sqls[self::SPECIFICATION_ORDER]);
        } else {
            $orderBy = 'ORDER BY (SELECT 1)';
        }

        // add a column for row_number() using the order specification
        $parameters[self::SPECIFICATION_SELECT][$parameterIndex][] = ['ROW_NUMBER() OVER (' . $orderBy . ')', '[__ZEND_ROW_NUMBER]'];

        $sqls[self::SPECIFICATION_SELECT] = $this->createSqlFromSpecificationAndParameters(
            $this->specifications[self::SPECIFICATION_SELECT],
            $parameters[self::SPECIFICATION_SELECT]
        );
    }
}
