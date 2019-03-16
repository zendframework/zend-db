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

class CreateTable extends AbstractSql implements SqlInterface
{
    public const COLUMNS     = 'columns';
    public const CONSTRAINTS = 'constraints';
    public const TABLE       = 'table';

    /** @var Column\ColumnInterface[] */
    protected $columns = [];

    /** @var string[] */
    protected $constraints = [];

    /** @var bool */
    protected $isTemporary = false;

    /**
     * {@inheritDoc}
     */
    protected $specifications = [
        self::TABLE => 'CREATE %1$sTABLE %2$s (',
        self::COLUMNS  => [
            "\n    %1\$s" => [
                [1 => '%1$s', 'combinedby' => ",\n    "]
            ]
        ],
        'combinedBy' => ",",
        self::CONSTRAINTS => [
            "\n    %1\$s" => [
                [1 => '%1$s', 'combinedby' => ",\n    "]
            ]
        ],
        'statementEnd' => '%1$s',
    ];

    /** @var string */
    protected $table = '';

    /**
     * @param string|TableIdentifier $table
     * @param bool   $isTemporary
     */
    public function __construct($table = '', bool $isTemporary = false)
    {
        $this->table = $table;
        $this->setTemporary($isTemporary);
    }

    /**
     * @param bool $temporary
     *
     * @return self Provides a fluent interface
     */
    public function setTemporary(bool $temporary) : self
    {
        $this->isTemporary = (bool) $temporary;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTemporary() : bool
    {
        return $this->isTemporary;
    }

    /**
     * @param string $name
     * @return self Provides a fluent interface
     */
    public function setTable(string $name) : self
    {
        $this->table = $name;

        return $this;
    }

    /**
     * @param ColumnInterface $column
     * @return self Provides a fluent interface
     */
    public function addColumn(ColumnInterface $column) : self
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * @param ConstraintInterface $constraint
     * @return self Provides a fluent interface
     */
    public function addConstraint(ConstraintInterface $constraint) : self
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @param string|null $key
     *
     * @return array
     */
    public function getRawState(?string $key = null) : array
    {
        $rawState = [
            self::COLUMNS     => $this->columns,
            self::CONSTRAINTS => $this->constraints,
            self::TABLE       => $this->table,
        ];

        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return string[]
     */
    protected function processTable(?PlatformInterface $adapterPlatform = null) : array
    {
        return [
            $this->isTemporary ? 'TEMPORARY ' : '',
            $this->resolveTable($this->table, $adapterPlatform),
        ];
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return string[][]|void
     */
    protected function processColumns(?PlatformInterface $adapterPlatform = null)
    {
        if (! $this->columns) {
            return;
        }

        $sqls = [];

        foreach ($this->columns as $column) {
            $sqls[] = $this->processExpression($column, $adapterPlatform);
        }

        return [$sqls];
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return array|string|void
     */
    protected function processCombinedby(?PlatformInterface $adapterPlatform = null)
    {
        if ($this->constraints && $this->columns) {
            return $this->specifications['combinedBy'];
        }
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return string[][]|void
     */
    protected function processConstraints(?PlatformInterface $adapterPlatform = null)
    {
        if (! $this->constraints) {
            return;
        }

        $sqls = [];

        foreach ($this->constraints as $constraint) {
            $sqls[] = $this->processExpression($constraint, $adapterPlatform);
        }

        return [$sqls];
    }

    /**
     * @param PlatformInterface $adapterPlatform
     *
     * @return string[]
     */
    protected function processStatementEnd(?PlatformInterface $adapterPlatform = null) : array
    {
        return ["\n)"];
    }
}
