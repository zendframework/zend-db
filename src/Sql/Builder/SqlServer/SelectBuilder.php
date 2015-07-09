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
    protected function build_Limit(Select $sqlObject, Context $context, &$sqls = null)
    {
        return;
    }

    protected function build_Offset(Select $sqlObject, Context $context, &$sqls = null)
    {
        $LIMIT = $sqlObject->limit;
        $OFFSET = $sqlObject->offset;
        if ($LIMIT === null && $OFFSET === null) {
            return;
        }

        $selectParameters = $sqls['select']['params'];

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
        $SSS = $sqls['select'];
        $SSS['spec']['format'] = 'SELECT %1$s FROM (';

        array_unshift($sqls, [
            'spec' => $SSS['spec'],
            'params' => $selectParameters,
        ]);

        if ($parameterContainer = $context->getParameterContainer()) {
            // create bottom part of query, with offset and limit using row_number
            $sqls[] =
                    ') AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN '
                  . $context->getDriver()->formatParameterName('offset')
                  . '+1 AND '
                  . $context->getDriver()->formatParameterName('limit')
                  . '+'
                  . $context->getDriver()->formatParameterName('offsetForSum');

            $parameterContainer->offsetSet('offset', $OFFSET);
            $parameterContainer->offsetSet('limit', $LIMIT);
            $parameterContainer->offsetSetReference('offsetForSum', 'offset');
        } else {
            $sqls[] =
                    ') AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN '
                  . (int) $OFFSET
                  . '+1 AND '
                  . (int) $LIMIT
                  . '+'
                  . (int) $OFFSET;
        }

        if (isset($sqls['order'])) {
            $orderBy = $this->buildSqlString($sqls['order'], $context);
            unset($sqls['order']);
        } else {
            $orderBy = 'ORDER BY (SELECT 1)';
        }

        // add a column for row_number() using the order specification
        $parameters = $sqls['select']['params'];
        $parameters[$parameterIndex][] = [
            'ROW_NUMBER() OVER (' . $orderBy . ')',
            '[__ZEND_ROW_NUMBER]'
        ];

        $sqls['select'] = [
            'spec' => $sqls['select']['spec'],
            'params' => $parameters,
        ];
    }
}
