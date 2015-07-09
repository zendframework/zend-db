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
use Zend\Db\Sql\Builder\Builder;
use Zend\Db\Sql\Builder\Context;

class SelectBuilder extends BaseBuilder
{
    /**
     * {@inheritDoc}
     */
    public function __construct(Builder $platformBuilder)
    {
        parent::__construct($platformBuilder);
        $asSpec = [
            'byCount' => [
                1 => '%1$s', 2 => '%1$s %2$s'
            ],
        ];
        $this->selectColumnsTableSpecification['byArgNumber'][2] = $asSpec;
        $this->selectFullSpecification['byArgNumber'][3] = $asSpec;
        $this->joinsSpecification['forEach']['byArgNumber'][2] = $asSpec;
    }

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
        $SSS = $sqls['select'];
        $SSS['spec']['format'] = 'SELECT %1$s FROM (SELECT b.%1$s, rownum b_rownum FROM (';

        array_unshift($sqls, [
            'spec' => $SSS['spec'],
            'params' => $selectParameters,
        ]);

        if ($parameterContainer = $context->getParameterContainer()) {
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
        $parameters = $sqls['select']['params'];
        $sqls['select'] = [
            'spec' => $sqls['select']['spec'],
            'params' => $parameters,
        ];
    }
}
