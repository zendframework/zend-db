<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl\Constraint;

abstract class AbstractConstraint implements ConstraintInterface
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @param null|string|array $columns
     * @param null|string $name
     */
    public function __construct($columns = null, $name = null)
    {
        if ($columns) {
            $this->setColumns($columns);
        }

        $this->setName($name);
    }

    /**
     * @param  string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  null|string|array $columns
     * @return self
     */
    public function setColumns($columns)
    {
        $this->columns = (array) $columns;

        return $this;
    }

    /**
     * @param  string $column
     * @return self
     */
    public function addColumn($column)
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumns()
    {
        return $this->columns;
    }
}
