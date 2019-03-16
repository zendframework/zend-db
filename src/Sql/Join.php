<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

use Countable;
use Iterator;

/**
 * Aggregate JOIN specifications.
 *
 * Each specification is an array with the following keys:
 *
 * - name: the JOIN name
 * - on: the table on which the JOIN occurs
 * - columns: the columns to include with the JOIN operation; defaults to
 *   `Select::SQL_STAR`.
 * - type: the type of JOIN being performed; see the `JOIN_*` constants;
 *   defaults to `JOIN_INNER`
 */
class Join implements Iterator, Countable
{
    public const JOIN_INNER       = 'inner';
    public const JOIN_OUTER       = 'outer';
    public const JOIN_LEFT        = 'left';
    public const JOIN_RIGHT       = 'right';
    public const JOIN_RIGHT_OUTER = 'right outer';
    public const JOIN_LEFT_OUTER  = 'left outer';

    /** @var int */
    private $position = 0;

    /** @var array */
    protected $joins = [];

    public function rewind() : void
    {
        $this->position = 0;
    }

    public function current() : array
    {
        return $this->joins[$this->position];
    }

    public function key() : int
    {
        return $this->position;
    }

    public function next() : void
    {
        ++$this->position;
    }

    public function valid() : bool
    {
        return isset($this->joins[$this->position]);
    }

    public function getJoins() : array
    {
        return $this->joins;
    }

    /**
     * @param string|array|TableIdentifier $name A table name on which to join, or a single
     *     element associative array, of the form alias => table, or TableIdentifier instance
     * @param string|Predicate\Expression $on A specification describing the fields to join on.
     * @param string|string[]|int|int[] $columns A single column name, an array
     *     of column names, or (a) specification(s) such as SQL_STAR representing
     *     the columns to join.
     * @param string $type The JOIN type to use; see the JOIN_* constants.
     * @return self
     * @throws Exception\InvalidArgumentException for invalid $name values.
     */
    public function join($name, $on, $columns = [Select::SQL_STAR], string $type = Join::JOIN_INNER) : self
    {
        if (is_array($name) && (! is_string(key($name)) || count($name) !== 1)) {
            throw new Exception\InvalidArgumentException(
                sprintf("join() expects '%s' as a single element associative array", array_shift($name))
            );
        }

        if (! is_array($columns)) {
            $columns = [$columns];
        }

        $this->joins[] = [
            'name'    => $name,
            'on'      => $on,
            'columns' => $columns,
            'type'    => $type ? $type : self::JOIN_INNER
        ];

        return $this;
    }

    public function reset() : self
    {
        $this->joins = [];
        return $this;
    }

    public function count() : int
    {
        return count($this->joins);
    }
}
