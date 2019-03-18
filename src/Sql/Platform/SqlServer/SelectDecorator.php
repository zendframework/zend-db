<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Platform\SqlServer;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Select;
use function array_shift;
use function array_unshift;
use function array_values;
use function current;
use function strpos;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /** @var Select */
    protected $subject;

    /**
     * @param Select $select
     */
    public function setSubject($select)
    {
        $this->subject = $select;
    }

    protected function localizeVariables() : void
    {
        parent::localizeVariables();
        // set specifications
        unset($this->specifications[self::LIMIT]);
        unset($this->specifications[self::OFFSET]);

        $this->specifications['LIMITOFFSET'] = null;
    }

    protected function processLimitOffset(
        PlatformInterface   $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null,
        &$sqls,
        &$parameters
    ) : void {
        if ($this->limit === null && $this->offset === null) {
            return;
        }

        $selectParameters = $parameters[self::SELECT];

        /** if this is a DISTINCT query then real SELECT part goes to second element in array **/
        $parameterIndex = 0;
        if ($selectParameters[0] === 'DISTINCT') {
            unset($selectParameters[0]);
            $selectParameters = array_values($selectParameters);
            $parameterIndex = 1;
        }

        $starSuffix = $platform->getIdentifierSeparator() . self::SQL_STAR;
        foreach ($selectParameters[0] as $i => $columnParameters) {
            if ($columnParameters[0] === self::SQL_STAR
                || (isset($columnParameters[1]) && $columnParameters[1] === self::SQL_STAR)
                || strpos($columnParameters[0], $starSuffix)
            ) {
                $selectParameters[0] = [[self::SQL_STAR]];
                break;
            }
            if (isset($columnParameters[1])) {
                array_shift($columnParameters);
                $selectParameters[0][$i] = $columnParameters;
            }
        }

        // first, produce column list without compound names (using the AS portion only)
        array_unshift($sqls, $this->createSqlFromSpecificationAndParameters(
            ['SELECT %1$s FROM (' => current($this->specifications[self::SELECT])],
            $selectParameters
        ));

        if ($parameterContainer) {
            // create bottom part of query, with offset and limit using row_number
            $limitParamName = $driver->formatParameterName('limit');
            $offsetParamName = $driver->formatParameterName('offset');
            $offsetForSumParamName = $driver->formatParameterName('offsetForSum');
            // @codingStandardsIgnoreStart
            $sqls[] = ') AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN '
                . $offsetParamName . '+1 AND ' . $limitParamName . '+' . $offsetForSumParamName;
            // @codingStandardsIgnoreEnd
            $parameterContainer->offsetSet('offset', $this->offset);
            $parameterContainer->offsetSet('limit', $this->limit);
            $parameterContainer->offsetSetReference('offsetForSum', 'offset');
        } else {
            // @codingStandardsIgnoreStart
            $sqls[] = ') AS [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION] WHERE [ZEND_SQL_SERVER_LIMIT_OFFSET_EMULATION].[__ZEND_ROW_NUMBER] BETWEEN '
                . (int) $this->offset . '+1 AND '
                . (int) $this->limit . '+' . (int) $this->offset;
            // @codingStandardsIgnoreEnd
        }

        if (isset($sqls[self::ORDER])) {
            $orderBy = $sqls[self::ORDER];
            unset($sqls[self::ORDER]);
        } else {
            $orderBy = 'ORDER BY (SELECT 1)';
        }

        // add a column for row_number() using the order specification
        $parameters[self::SELECT][$parameterIndex][] = ['ROW_NUMBER() OVER (' . $orderBy . ')', '[__ZEND_ROW_NUMBER]'];

        $sqls[self::SELECT] = $this->createSqlFromSpecificationAndParameters(
            $this->specifications[self::SELECT],
            $parameters[self::SELECT]
        );
    }
}
