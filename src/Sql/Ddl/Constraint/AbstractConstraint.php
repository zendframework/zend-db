<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl\Constraint;

abstract class AbstractConstraint implements ConstraintInterface
{
    /** @var string */
    protected $columnSpecification = ' (%s)';

    /** @var string */
    protected $namedSpecification = 'CONSTRAINT %s ';

    /** @var string */
    protected $specification = '';

    /** @var string */
    protected $name = '';

    /** @var array */
    protected $columns = [];

    /**
     * @param null|string|array $columns
     * @param string $name
     */
    public function __construct(?$columns = null, string $name = '')
    {
        if ($columns) {
            $this->setColumns($columns);
        }

        $this->setName($name);
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param null|string|array $columns
     * @return $this
     */
    public function setColumns(?$columns) : self
    {
        $this->columns = (array) $columns;

        return $this;
    }

    public function addColumn(string $column) : self
    {
        $this->columns[] = $column;
        return $this;
    }

    public function getColumns() : array
    {
        return $this->columns;
    }

    public function getExpressionData() : array
    {
        $colCount = count($this->columns);
        $newSpecTypes = [];
        $values = [];
        $newSpec = '';

        if ($this->name) {
            $newSpec .= $this->namedSpecification;
            $values[] = $this->name;
            $newSpecTypes[] = self::TYPE_IDENTIFIER;
        }

        $newSpec .= $this->specification;

        if ($colCount) {
            $values = array_merge($values, $this->columns);
            $newSpecParts = array_fill(0, $colCount, '%s');
            $newSpecTypes = array_merge($newSpecTypes, array_fill(0, $colCount, self::TYPE_IDENTIFIER));
            $newSpec .= sprintf($this->columnSpecification, implode(', ', $newSpecParts));
        }

        return [[
            $newSpec,
            $values,
            $newSpecTypes,
        ]];
    }
}
