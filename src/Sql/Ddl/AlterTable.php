<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\AbstractSql;
use Zend\Db\Sql\Ddl\Column\ColumnInterface;
use Zend\Db\Sql\Ddl\Constraint\ConstraintInterface;
use Zend\Db\Sql\TableIdentifier;

class AlterTable extends AbstractSql implements SqlInterface
{
    public const ADD_COLUMNS      = 'addColumns';
    public const ADD_CONSTRAINTS  = 'addConstraints';
    public const CHANGE_COLUMNS   = 'changeColumns';
    public const DROP_COLUMNS     = 'dropColumns';
    public const DROP_CONSTRAINTS = 'dropConstraints';
    public const TABLE            = 'table';

    /** @var array */
    protected $addColumns = [];

    /** @var array */
    protected $addConstraints = [];

    /** @var array */
    protected $changeColumns = [];

    /** @var array */
    protected $dropColumns = [];

    /** @var array */
    protected $dropConstraints = [];

    /**
     * Specifications for Sql String generation
     * @var array
     */
    protected $specifications = [
        self::TABLE => "ALTER TABLE %1\$s\n",
        self::ADD_COLUMNS  => [
            '%1$s' => [
                [1 => "ADD COLUMN %1\$s,\n", 'combinedby' => '']
            ]
        ],
        self::CHANGE_COLUMNS  => [
            '%1$s' => [
                [2 => "CHANGE COLUMN %1\$s %2\$s,\n", 'combinedby' => ''],
            ]
        ],
        self::DROP_COLUMNS  => [
            '%1$s' => [
                [1 => "DROP COLUMN %1\$s,\n", 'combinedby' => ''],
            ]
        ],
        self::ADD_CONSTRAINTS  => [
            '%1$s' => [
                [1 => "ADD %1\$s,\n", 'combinedby' => ''],
            ]
        ],
        self::DROP_CONSTRAINTS  => [
            '%1$s' => [
                [1 => "DROP CONSTRAINT %1\$s,\n", 'combinedby' => ''],
            ]
        ]
    ];

    /** @var string */
    protected $table = '';

    /**
     * @param string|TableIdentifier $table
     */
    public function __construct($table = '')
    {
        $table ? $this->setTable($table) : null;
    }

    /**
     * @param string $name
     *
     * @return self Provides a fluent interface
     */
    public function setTable(string $name) : self
    {
        $this->table = $name;

        return $this;
    }

    /**
     * @param ColumnInterface $column
     *
     * @return self Provides a fluent interface
     */
    public function addColumn(ColumnInterface $column) : self
    {
        $this->addColumns[] = $column;

        return $this;
    }

    /**
     * @param string $name
     * @param ColumnInterface $column
     *
     * @return self Provides a fluent interface
     */
    public function changeColumn(string $name, ColumnInterface $column) : self
    {
        $this->changeColumns[$name] = $column;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return self Provides a fluent interface
     */
    public function dropColumn(string $name) : self
    {
        $this->dropColumns[] = $name;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return self Provides a fluent interface
     */
    public function dropConstraint(string $name) : self
    {
        $this->dropConstraints[] = $name;

        return $this;
    }

    /**
     * @param ConstraintInterface $constraint
     *
     * @return self Provides a fluent interface
     */
    public function addConstraint(ConstraintInterface $constraint) : self
    {
        $this->addConstraints[] = $constraint;

        return $this;
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function getRawState(?string $key = null) : array
    {
        $rawState = [
            self::TABLE => $this->table,
            self::ADD_COLUMNS => $this->addColumns,
            self::DROP_COLUMNS => $this->dropColumns,
            self::CHANGE_COLUMNS => $this->changeColumns,
            self::ADD_CONSTRAINTS => $this->addConstraints,
            self::DROP_CONSTRAINTS => $this->dropConstraints,
        ];

        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    protected function processTable(?PlatformInterface $adapterPlatform = null) : array
    {
        return [$this->resolveTable($this->table, $adapterPlatform)];
    }

    protected function processAddColumns(?PlatformInterface $adapterPlatform = null) : array
    {
        $sqls = [];
        foreach ($this->addColumns as $column) {
            $sqls[] = $this->processExpression($column, $adapterPlatform);
        }

        return [$sqls];
    }

    protected function processChangeColumns(?PlatformInterface $adapterPlatform = null) : array
    {
        $sqls = [];
        foreach ($this->changeColumns as $name => $column) {
            $sqls[] = [
                $adapterPlatform->quoteIdentifier($name),
                $this->processExpression($column, $adapterPlatform)
            ];
        }

        return [$sqls];
    }

    protected function processDropColumns(?PlatformInterface $adapterPlatform = null) : array
    {
        $sqls = [];
        foreach ($this->dropColumns as $column) {
            $sqls[] = $adapterPlatform->quoteIdentifier($column);
        }

        return [$sqls];
    }

    protected function processAddConstraints(?PlatformInterface $adapterPlatform = null) : array
    {
        $sqls = [];
        foreach ($this->addConstraints as $constraint) {
            $sqls[] = $this->processExpression($constraint, $adapterPlatform);
        }

        return [$sqls];
    }

    protected function processDropConstraints(?PlatformInterface $adapterPlatform = null) : array
    {
        $sqls = [];
        foreach ($this->dropConstraints as $constraint) {
            $sqls[] = $adapterPlatform->quoteIdentifier($constraint);
        }

        return [$sqls];
    }
}
