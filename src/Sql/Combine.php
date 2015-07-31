<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

/**
 * Combine SQL statement - allows combining multiple select statements into one
 *
 * @property array $columns
 * @property SelectableInterface[][] $combine
 */
class Combine extends AbstractSqlObject implements PreparableSqlObjectInterface, SelectableInterface
{
    const COMBINE_UNION = 'union';
    const COMBINE_EXCEPT = 'except';
    const COMBINE_INTERSECT = 'intersect';


    /**
     * @var SelectableInterface[][]
     */
    protected $combine = [];

    protected $__getProperties = [
        'combine',
        'columns'
    ];

    /**
     * @param Select|array|null $select
     * @param string            $type
     * @param string            $modifier
     */
    public function __construct($select = null, $type = self::COMBINE_UNION, $modifier = '')
    {
        parent::__construct();
        if ($select) {
            $this->combine($select, $type, $modifier);
        }
    }

    /**
     * Create combine clause
     *
     * @param SelectableInterface|array $select
     * @param string $type
     * @param string $modifier
     *
     * @return self
     */
    public function combine($select, $type = self::COMBINE_UNION, $modifier = '')
    {
        if (is_array($select)) {
            foreach ($select as $combine) {
                if ($combine instanceof SelectableInterface) {
                    $this->combine($combine, $type, $modifier);
                } elseif (is_string(key($combine))) {
                    $this->combine(
                        $combine['select'],
                        isset($combine['type']) ? $combine['type'] : $type,
                        isset($combine['modifier']) ? $combine['modifier'] : $modifier
                    );
                } else {
                    $this->combine(
                        $combine[0],
                        isset($combine[1]) ? $combine[1] : $type,
                        isset($combine[2]) ? $combine[2] : $modifier
                    );
                }
            }
            return $this;
        }

        if (! $select instanceof SelectableInterface) {
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
     * @param SelectableInterface|array $select
     * @param string       $modifier
     *
     * @return self
     */
    public function union($select, $modifier = '')
    {
        return $this->combine($select, self::COMBINE_UNION, $modifier);
    }

    /**
     * Create except clause
     *
     * @param SelectableInterface|array $select
     * @param string       $modifier
     *
     * @return self
     */
    public function except($select, $modifier = '')
    {
        return $this->combine($select, self::COMBINE_EXCEPT, $modifier);
    }

    /**
     * Create intersect clause
     *
     * @param SelectableInterface|array $select
     * @param string $modifier
     * @return self
     */
    public function intersect($select, $modifier = '')
    {
        return $this->combine($select, self::COMBINE_INTERSECT, $modifier);
    }

    /**
     * @return $this
     */
    public function alignColumns()
    {
        if (!$this->combine) {
            return $this;
        }

        $allColumns = [];
        foreach ($this->combine as $combine) {
            $allColumns = array_merge(
                $allColumns,
                $combine['select']->columns
            );
        }

        foreach ($this->combine as $combine) {
            $combineColumns = $combine['select']->columns;
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

    public function __get($name)
    {
        if ($name == 'columns') {
            return $this->combine
                    ? $this->combine[0]['select']->columns
                    : [];
        }
        return parent::__get($name);
    }
}
