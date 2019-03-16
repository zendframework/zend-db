<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Platform\IbmDb2;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Select;
use function array_push;
use function array_shift;
use function array_unshift;
use function current;
use function preg_match;
use function sprintf;
use function strpos;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /** @var bool */
    protected $isSelectContainDistinct = false;

    /** @var Select */
    protected $subject;

     /** @var bool */
    protected $supportsLimitOffset = false;


   /**
     * @return bool
     */
    public function getIsSelectContainDistinct() : bool
    {
        return $this->isSelectContainDistinct;
    }

    /**
     * @param bool $isSelectContainDistinct
     */
    public function setIsSelectContainDistinct(bool $isSelectContainDistinct) : void
    {
        $this->isSelectContainDistinct = $isSelectContainDistinct;
    }

    /**
     * @param Select $select
     */
    public function setSubject($select)
    {
        $this->subject = $select;
    }

    /**
     * @return bool
     */
    public function getSupportsLimitOffset() : bool
    {
        return $this->supportsLimitOffset;
    }

    /**
     * @param bool $supportsLimitOffset
     */
    public function setSupportsLimitOffset(bool $supportsLimitOffset) : void
    {
        $this->supportsLimitOffset = $supportsLimitOffset;
    }

    /**
     * {@inheritDoc}
     */
    protected function renderTable(string $table, ?string $alias = null) : string
    {
        return $table . ' ' . $alias;
    }

    protected function localizeVariables() : void
    {
        parent::localizeVariables();
        // set specifications
        unset($this->specifications[self::LIMIT]);
        unset($this->specifications[self::OFFSET]);

        $this->specifications['LIMITOFFSET'] = null;
    }

    /**
     * @param PlatformInterface  $platform
     * @param DriverInterface    $driver
     * @param ParameterContainer $parameterContainer
     * @param array              $sqls
     * @param array              $parameters
     */
    protected function processLimitOffset(
        PlatformInterface  $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null,
        array              &$sqls,
        array              &$parameters
    ) : void {
        if ($this->limit === null && $this->offset === null) {
            return;
        }

        if ($this->supportsLimitOffset) {
            // Note: db2_prepare/db2_execute fails with positional parameters, for LIMIT & OFFSET
            $limit = (int) $this->limit;
            if (! $limit) {
                return;
            }

            $offset = (int) $this->offset;
            if ($offset) {
                $sqls[] = sprintf('LIMIT %s OFFSET %s', $limit, $offset);
                return;
            }

            $sqls[] = sprintf('LIMIT %s', $limit);
            return;
        }

        $selectParameters = $parameters[self::SELECT];

        $starSuffix = $platform->getIdentifierSeparator() . self::SQL_STAR;
        foreach ($selectParameters[0] as $i => $columnParameters) {
            if ($columnParameters[0] == self::SQL_STAR
                || (isset($columnParameters[1]) && $columnParameters[1] == self::SQL_STAR)
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

        if (preg_match('/DISTINCT/i', $sqls[0])) {
            $this->setIsSelectContainDistinct(true);
        }

        if ($parameterContainer) {
            // create bottom part of query, with offset and limit using row_number
            $limitParamName        = $driver->formatParameterName('limit');
            $offsetParamName       = $driver->formatParameterName('offset');

            $sqls[] = sprintf(
            // @codingStandardsIgnoreStart
                ') AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN %s AND %s',
                // @codingStandardsIgnoreEnd
                $offsetParamName,
                $limitParamName
            );

            if ((int) $this->offset > 0) {
                $parameterContainer->offsetSet('offset', (int) $this->offset + 1);
            } else {
                $parameterContainer->offsetSet('offset', (int) $this->offset);
            }

            $parameterContainer->offsetSet('limit', (int) $this->limit + (int) $this->offset);
        } else {
            if ((int) $this->offset > 0) {
                $offset = (int) $this->offset + 1;
            } else {
                $offset = (int) $this->offset;
            }

            $sqls[] = sprintf(
            // @codingStandardsIgnoreStart
                ') AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN %d AND %d',
                // @codingStandardsIgnoreEnd
                $offset,
                (int)$this->limit + (int)$this->offset
            );
        }

        if (isset($sqls[self::ORDER])) {
            $orderBy = $sqls[self::ORDER];
            unset($sqls[self::ORDER]);
        } else {
            $orderBy = '';
        }

        // add a column for row_number() using the order specification //dense_rank()
        if ($this->getIsSelectContainDistinct()) {
            $parameters[self::SELECT][0][] = ['DENSE_RANK() OVER (' . $orderBy . ')', 'ZEND_DB_ROWNUM'];
        } else {
            $parameters[self::SELECT][0][] = ['ROW_NUMBER() OVER (' . $orderBy . ')', 'ZEND_DB_ROWNUM'];
        }

        $sqls[self::SELECT] = $this->createSqlFromSpecificationAndParameters(
            $this->specifications[self::SELECT],
            $parameters[self::SELECT]
        );
    }
}
