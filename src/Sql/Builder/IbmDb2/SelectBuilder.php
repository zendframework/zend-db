<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\IbmDb2;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Builder\sql92\SelectBuilder as BaseBuilder;
use Zend\Db\Sql\Builder\Context;

class SelectBuilder extends BaseBuilder
{
    /**
     * @see Select::renderTable
     */
    protected function renderTable($table, $alias = null)
    {
        return $table . ' ' . $alias;
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
            if ($columnParameters[0] == Select::SQL_STAR
                || (isset($columnParameters[1]) && $columnParameters[1] == Select::SQL_STAR)
                || strpos($columnParameters[0], $starSuffix)
            ) {
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

        $offset = ((int) $OFFSET > 0) ? (int) $OFFSET + 1 : (int) $OFFSET;
        $limit  = (int) $LIMIT + (int) $OFFSET;

        if ($context->getParameterContainer()) {
            $context->getParameterContainer()->offsetSet('offset', $offset);
            $context->getParameterContainer()->offsetSet('limit', $limit);

            $limit  = $context->getDriver()->formatParameterName('limit');
            $offset = $context->getDriver()->formatParameterName('offset');
        }

        array_push($sqls, sprintf(
            ") AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN %s AND %s",
            $offset,
            $limit
        ));

        if (isset($sqls[self::SPECIFICATION_ORDER])) {
            $orderBy = $sqls[self::SPECIFICATION_ORDER];
            unset($sqls[self::SPECIFICATION_ORDER]);
        } else {
            $orderBy = '';
        }

        // add a column for row_number() using the order specification //dense_rank()
        $parameters[self::SPECIFICATION_SELECT][0][] = (preg_match('/DISTINCT/i', $sqls[0]))
                ? ['DENSE_RANK() OVER (' . $orderBy . ')', 'ZEND_DB_ROWNUM']
                : ['ROW_NUMBER() OVER (' . $orderBy . ')', 'ZEND_DB_ROWNUM'];

        $sqls[self::SPECIFICATION_SELECT] = $this->createSqlFromSpecificationAndParameters(
            $this->specifications[self::SPECIFICATION_SELECT],
            $parameters[self::SPECIFICATION_SELECT]
        );
    }
}
