<?php

declare(strict_types=1);

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

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
    const COLUMNS = 'columns';
    const COMBINE = 'combine';
    const COMBINE_UNION = 'union';
    const COMBINE_EXCEPT = 'except';
    const COMBINE_INTERSECT = 'intersect';

    /**
     * @var string[]
     */
    protected $specifications = [
        self::COMBINE => '%1$s (%2$s) ',
    ];

    /**
     * @var Select[][]
     */
    private $combine = [];

    /**
     * @param Select|array|null $select
     * @param string            $type
     * @param string            $modifier
     */
    public function __construct($select = null, ?string $type = self::COMBINE_UNION, ?string $modifier = '')
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
    public function combine($select, ?string $type = self::COMBINE_UNION, ?string $modifier = '') : self
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

        $this->combine[] = [
            'select' => $select,
            'type' => $type,
            'modifier' => $modifier
        ];

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
    public function union($select, ?string $modifier = '') : self
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
    public function except($select, ?string $modifier = '') : self
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
    public function intersect($select, ?string $modifier = '') : self
    {
        return $this->combine($select, self::COMBINE_INTERSECT, $modifier);
    }

    /**
     * Build sql string
     *
     * @param PlatformInterface  $platform
     * @param DriverInterface    $driver
     * @param ParameterContainer $parameterContainer
     *
     * @return string|void
     */
    protected function buildSqlString(
        PlatformInterface  $platform,
        DriverInterface    $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if (! $this->combine) {
            return;
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

    /**
     * @return self Provides a fluent interface
     */
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

    /**
     * Get raw state
     *
     * @param string $key
     *
     * @return array
     */
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
