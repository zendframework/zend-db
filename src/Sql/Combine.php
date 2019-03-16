<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use function array_key_exists;
use function array_merge;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function sprintf;
use function strtoupper;

/**
 * Combine SQL statement - allows combining multiple select statements into one
 */
class Combine extends AbstractPreparableSql
{
    public const COLUMNS = 'columns';
    public const COMBINE = 'combine';
    public const COMBINE_UNION = 'union';
    public const COMBINE_EXCEPT = 'except';
    public const COMBINE_INTERSECT = 'intersect';

    /** @var string[] */
    protected $specifications = [
        self::COMBINE => '%1$s (%2$s) ',
    ];

    /** @var Select[][] */
    private $combine = [];

    /**
     * @param Select|array|null $select
     * @param string            $type
     * @param string            $modifier
     */
    public function __construct(?$select = null, string $type = self::COMBINE_UNION, string $modifier = '')
    {
        if ($select) {
            $this->combine($select, $type, $modifier);
        }
    }

    /**
     * Create combine clause
     *
     * @param Select|array $select
     * @param string $type
     * @param string $modifier
     *
     * @return self Provides a fluent interface
     *
     * @throws Exception\InvalidArgumentException
     */
    public function combine($select, string $type = self::COMBINE_UNION, string $modifier = '') : self
    {
        if (is_array($select)) {
            foreach ($select as $combine) {
                if ($combine instanceof Select) {
                    $combine = [$combine];
                }

                $this->combine(
                    $combine[0],
                    isset($combine[1]) ? $combine[1] : $type,
                    isset($combine[2]) ? $combine[2] : $modifier
                );
            }
            return $this;
        }

        if (! $select instanceof Select) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$select must be a array or instance of Select, "%s" given',
                is_object($select) ? get_class($select) : gettype($select)
            ));
        }

        $this->combine[] = compact('select', 'type', 'modifier');

        return $this;
    }

    /**
     * Create union clause
     *
     * @param Select|array $select
     * @param string       $modifier
     *
     * @return self
     */
    public function union($select, string $modifier = '') : self
    {
        return $this->combine($select, self::COMBINE_UNION, $modifier);
    }

    /**
     * Create except clause
     *
     * @param Select|array $select
     * @param string       $modifier
     *
     * @return self
     */
    public function except($select, string $modifier = '') : self
    {
        return $this->combine($select, self::COMBINE_EXCEPT, $modifier);
    }

    /**
     * Create intersect clause
     *
     * @param Select|array $select
     * @param string $modifier
     *
     * @return self
     */
    public function intersect($select, string $modifier = '') : self
    {
        return $this->combine($select, self::COMBINE_INTERSECT, $modifier);
    }

    protected function buildSqlString(
        PlatformInterface  $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) : ?string {
        if (! $this->combine) {
            return null;
        }

        $sql = '';

        foreach ($this->combine as $i => $combine) {
            $type = $i == 0
                    ? ''
                    : strtoupper($combine['type'] . ($combine['modifier'] ? ' ' . $combine['modifier'] : ''));
            $select = $this->processSubSelect($combine['select'], $platform, $driver, $parameterContainer);
            $sql .= sprintf(
                $this->specifications[self::COMBINE],
                $type,
                $select
            );
        }

        return trim($sql, ' ');
    }

    public function alignColumns() : self
    {
        if (! $this->combine) {
            return $this;
        }

        $allColumns = [];
        foreach ($this->combine as $combine) {
            $allColumns = array_merge(
                $allColumns,
                $combine['select']->getRawState(self::COLUMNS)
            );
        }

        foreach ($this->combine as $combine) {
            $combineColumns = $combine['select']->getRawState(self::COLUMNS);
            $aligned = [];
            foreach ($allColumns as $alias => $column) {
                $aligned[$alias] = isset($combineColumns[$alias])
                    ? $combineColumns[$alias]
                    : new Predicate\Expression('NULL');
            }
            $combine['select']->columns($aligned, false);
        }

        return $this;
    }

    public function getRawState(?string $key = null) : array
    {
        $rawState = [
            self::COMBINE => $this->combine,
            self::COLUMNS => $this->combine
                                ? $this->combine[0]['select']->getRawState(self::COLUMNS)
                                : [],
        ];

        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }
}
