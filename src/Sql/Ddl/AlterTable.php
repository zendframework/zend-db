<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Sql\AbstractSqlObject;
use Zend\Db\Sql\TableIdentifier;

/**
 * @property null|string|array|TableIdentifier $table
 * @property array $addColumns
 * @property array $dropColumns
 * @property array $changeColumns
 * @property array $addConstraints
 * @property array $dropConstraints
 */
class AlterTable extends AbstractSqlObject
{
    /**
     * @var array
     */
    protected $addColumns = [];

    /**
     * @var array
     */
    protected $addConstraints = [];

    /**
     * @var array
     */
    protected $changeColumns = [];

    /**
     * @var array
     */
    protected $dropColumns = [];

    /**
     * @var array
     */
    protected $dropConstraints = [];


    /**
     * @var TableIdentifier
     */
    protected $table;

    protected $__getProperties = [
        'table',
        'addColumns',
        'dropColumns',
        'changeColumns',
        'addConstraints',
        'dropConstraints',
    ];

    /**
     * @param string|array|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        parent::__construct();
        $this->setTable($table);
    }

    /**
     * @param  string|array|TableIdentifier $name
     * @return self
     */
    public function setTable($name)
    {
        $this->table = TableIdentifier::factory($name);

        return $this;
    }

    /**
     * @param  Column\ColumnInterface $column
     * @return self
     */
    public function addColumn(Column\ColumnInterface $column)
    {
        $this->addColumns[] = $column;

        return $this;
    }

    /**
     * @param  string $name
     * @param  Column\ColumnInterface $column
     * @return self
     */
    public function changeColumn($name, Column\ColumnInterface $column)
    {
        $this->changeColumns[$name] = $column;

        return $this;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function dropColumn($name)
    {
        $this->dropColumns[] = $name;

        return $this;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function dropConstraint($name)
    {
        $this->dropConstraints[] = $name;

        return $this;
    }

    /**
     * @param  Constraint\ConstraintInterface $constraint
     * @return self
     */
    public function addConstraint(Constraint\ConstraintInterface $constraint)
    {
        $this->addConstraints[] = $constraint;

        return $this;
    }
}
