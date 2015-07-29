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
 * @property TableIdentifier $table
 * @property array $columns
 * @property array $constraints
 * @property bool $isTemporary
 */
class CreateTable extends AbstractSqlObject
{
    /**
     * @var Column\ColumnInterface[]
     */
    protected $columns = [];

    /**
     * @var string[]
     */
    protected $constraints = [];

    /**
     * @var bool
     */
    protected $isTemporary = false;

    protected $__getProperties = [
        'table',
        'columns',
        'constraints',
        'isTemporary',
    ];

    /**
     * @var TableIdentifier
     */
    protected $table;

    /**
     * @param string $table
     * @param bool   $isTemporary
     */
    public function __construct($table = null, $isTemporary = false)
    {
        parent::__construct();
        $this->setTable($table);
        $this->setTemporary($isTemporary);
    }

    /**
     * @param  bool $temporary
     * @return self
     */
    public function setTemporary($temporary)
    {
        $this->isTemporary = (bool) $temporary;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTemporary()
    {
        return $this->isTemporary;
    }

    /**
     * @param  string $name
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
        $this->columns[] = $column;
        return $this;
    }

    /**
     * @param  Constraint\ConstraintInterface $constraint
     * @return self
     */
    public function addConstraint(Constraint\ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;
        return $this;
    }
}
