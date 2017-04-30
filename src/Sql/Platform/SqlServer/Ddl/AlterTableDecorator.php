<?php
/**
 * @see       http://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Db\Sql\Platform\SqlServer\Ddl;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Ddl\Column\ColumnInterface;
use Zend\Db\Sql\Ddl\Column\Varbinary;
use Zend\Db\Sql\Exception\InvalidArgumentException;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class AlterTableDecorator extends AlterTable implements PlatformDecoratorInterface
{

    const RENAME_COLUMNS = "renameColumns";

    /**
     * @var AlterTable
     */
    protected $subject;

    /**
     * @var array
     */
    protected $specifications = [
        self::ADD_COLUMNS  => [
            "%1\$s" => [
                [2 => "ALTER TABLE %1\$s\n ADD %2\$s;", 'combinedby' => "\n"],
            ],
        ],
        self::RENAME_COLUMNS => [
            "%1\$s" => [
                [2 => "sp_rename '%1\$s', '%2\$s', 'COLUMN';\n", 'combinedby' => "\n"],
            ]
        ],
        self::CHANGE_COLUMNS  => [
            "%1\$s" => [
                [2 => "ALTER TABLE %1\$s\n ALTER COLUMN %2\$s;", 'combinedby' => "\n"],
            ],
        ],
        self::DROP_COLUMNS  => [
            "%1\$s" => [
                [2 => "ALTER TABLE %1\$s\n DROP COLUMN %2\$s;", 'combinedby' => "\n"],
            ],
        ],
        self::ADD_CONSTRAINTS  => [
            "%1\$s" => [
                [2 => "ALTER TABLE %1\$s\n ADD %2\$s;", 'combinedby' => "\n"],
            ],
        ],
        self::DROP_CONSTRAINTS  => [
            "%1\$s" => [
                [2 => "ALTER TABLE %1\$s\n DROP CONSTRAINT %2\$s;\n", 'combinedby' => "\n"],
            ],
        ],
    ];

    /**
     * @var int[]
     * @see https://msdn.microsoft.com/en-us/library/ms187742.aspx#Syntax
     */
    protected $columnOptionSortOrder = [
        'filestream'    => 0,
        'collate'       => 1,
        'identity'      => 2,
        'serial'        => 2,
        'autoincrement' => 2,
        'rowguidcol'    => 3,
        'sparse'        => 4,
        'encryptedwith' => 4,
        'maskedwith'    => 5,
    ];

    /**
     * @param AlterTable $subject
     *
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->subject->specifications = $this->specifications;

        return $this;
    }

    /**
     * @param string $sql
     * @return array
     */
    protected function getSqlInsertOffsets($sql)
    {
        $sqlLength   = strlen($sql);
        $insertStart = [];

        foreach (['NOT NULL', 'NULL', 'DEFAULT', 'UNIQUE', 'PRIMARY', 'REFERENCES'] as $option) {
            $insertAt = strpos($sql, ' ' . $option);

            if ($insertAt !== false) {
                switch ($option) {
                    case 'REFERENCES':
                        $insertStart[2] = !isset($insertStart[2]) ? $insertAt : $insertStart[2];
                    // no break
                    case 'PRIMARY':
                    case 'UNIQUE':
                        $insertStart[1] = !isset($insertStart[1]) ? $insertAt : $insertStart[1];
                    // no break
                    default:
                        $insertStart[0] = !isset($insertStart[0]) ? $insertAt : $insertStart[0];
                }
            }
        }

        foreach (range(0, 3) as $i) {
            $insertStart[$i] = isset($insertStart[$i]) ? $insertStart[$i] : $sqlLength;
        }

        return $insertStart;
    }

    /**
     * @param PlatformInterface|null $adapterPlatform
     * @return array
     */
    protected function processAddColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = [];
        /**
         * @var Column $column
         */
        foreach ($this->addColumns as $column) {
            $sql           = $this->processExpression($column, $adapterPlatform);
            $optionOffsets = $this->getSqlInsertOffsets($sql);
            $columnOptions = $column->getOptions();

            uksort($columnOptions, [$this, 'compareColumnOptions']);

            foreach ($columnOptions as $optionName => $optionValue) {
                $insert = '';

                if (!$optionValue) {
                    continue;
                }

                switch ($this->normalizeColumnOption($optionName)) {
                    case 'filestream':
                        $insert = ' FILESTREAM';
                        $offsetIndex = 0;
                        break;
                    case 'collate':
                        // collate syntax does not use quotes
                        $insert = ' COLLATE ' . $optionValue;
                        $offsetIndex = 0;
                        break;
                    case 'identity':
                    case 'serial':
                    case 'autoincrement':
                        $insert = ' IDENTITY ' . $this->normalizeIdentityOptionValue($optionValue);
                        $offsetIndex = 0;
                        break;
                    case 'rowguidcol':
                        $insert = ' ROWGUIDCOL';
                        $offsetIndex = 1;
                        break;
                    case 'sparse':
                        $insert = ' SPARSE';
                        $offsetIndex = 1;
                        break;
                    case 'encryptedwith':
                        $insert = ' ENCRYPTED WITH ' . $optionValue;
                        $offsetIndex = 1;
                        break;
                    case 'maskedwith':
                        $insert = ' MASKED WITH ' . $optionValue;
                        $offsetIndex = 1;
                        break;
                    case 'comment':
                        $insert = ' COMMENT ' . $adapterPlatform->quoteValue($optionValue);
                        $offsetIndex = 2;
                        break;
                }

                if ($insert) {
                    $offsetIndex = isset($offsetIndex) ? $offsetIndex : 0;
                    $sql = substr_replace($sql, $insert, $optionOffsets[$offsetIndex], 0);
                    $insertStartCount = count($optionOffsets);
                    for (; $offsetIndex < $insertStartCount; ++$offsetIndex) {
                        $optionOffsets[$offsetIndex] += strlen($insert);
                    }
                }
            }
            $sqls[] = [
                $adapterPlatform->quoteIdentifier($this->subject->table),
                $sql,
            ];
        }

        return [$sqls];
    }

    /**
     * @param PlatformInterface|null $adapterPlatform
     * @return array
     */
    protected function processChangeColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = [];
        /**
         * @var Column $column
         */
        foreach ($this->changeColumns as $name => $column) {
            $sql           = $this->processExpression($column, $adapterPlatform);
            $optionOffsets = $this->getSqlInsertOffsets($sql);
            $columnOptions = $column->getOptions();

            uksort($columnOptions, [$this, 'compareColumnOptions']);

            foreach ($columnOptions as $optionName => $optionValue) {
                $insert = '';

                if (!$optionValue) {
                    continue;
                }

                switch ($this->normalizeColumnOption($optionName)) {
                    case 'filestream':
                        $insert = ' FILESTREAM';
                        $offsetIndex = 0;
                        break;
                    case 'collate':
                        // collate syntax does not use quotes
                        $insert = ' COLLATE ' . $optionValue;
                        $offsetIndex = 0;
                        break;
                    case 'identity':
                    case 'serial':
                    case 'autoincrement':
                        $insert = ' IDENTITY ' . $this->normalizeIdentityOptionValue($optionValue);
                        $offsetIndex = 0;
                        break;
                    case 'rowguidcol':
                        $insert = ' ROWGUIDCOL';
                        $offsetIndex = 1;
                        break;
                    case 'sparse':
                        $insert = ' SPARSE';
                        $offsetIndex = 1;
                        break;
                    case 'encryptedwith':
                        $insert = ' ENCRYPTED WITH ' . $optionValue;
                        $offsetIndex = 1;
                        break;
                    case 'maskedwith':
                        $insert = ' MASKED WITH ' . $optionValue;
                        $offsetIndex = 1;
                        break;
                    case 'comment':
                        $insert = ' COMMENT ' . $adapterPlatform->quoteValue($optionValue);
                        $offsetIndex = 2;
                        break;
                }

                if ($insert) {
                    $offsetIndex = isset($offsetIndex) ? $offsetIndex : 0;
                    $sql = substr_replace($sql, $insert, $optionOffsets[$offsetIndex], 0);
                    $insertStartCount = count($optionOffsets);
                    for (; $offsetIndex < $insertStartCount; ++$offsetIndex) {
                        $optionOffsets[$offsetIndex] += strlen($insert);
                    }
                }
            }
            $sqls[] = [
                $adapterPlatform->quoteIdentifier($this->subject->table),
                $sql,
            ];
        }

        return [$sqls];
    }

    /**
     * @param PlatformInterface|null $adapterPlatform
     */
    protected function processRenameColumns(PlatformInterface $adapterPlatform = null)
    {
        $renameColumns = [];

        // because altered in format $alterTable->changeColumn('old_name', new Column('new_name'))
        /** @var ColumnInterface $column */
        foreach ($this->changeColumns as $oldName => $column) {
            if (strcmp($oldName, $column->getName()) !== 0) {
                $renameColumns[$oldName] = $column;
            }
        }

        $sqls = [];
        foreach ($renameColumns as $oldName => $column) {
            /** sp_ utility stored procedures do not quote identifiers unlike regular SQL syntax
             * @see https://docs.microsoft.com/en-us/sql/relational-databases/system-stored-procedures/sp-rename-transact-sql
             */
            $sqls[] = [
                $this->subject->table . '.' . $oldName,
                $column->getName(),
            ];
        }

        return [$sqls];
    }

    /**
     * @param PlatformInterface|null $adapterPlatform
     * @return array
     */
    protected function processDropColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = [];
        foreach ($this->dropColumns as $column) {
            $sqls[] = [
                $adapterPlatform->quoteIdentifier($this->subject->table),
                $adapterPlatform->quoteIdentifier($column),
            ];
        }

        return [$sqls];
    }

    /**
     * @param PlatformInterface|null $adapterPlatform
     * @return array
     */
    protected function processAddConstraints(PlatformInterface $adapterPlatform = null)
    {
        $sqls = [];
        foreach ($this->addConstraints as $constraint) {
            $sqls[] = [
                $adapterPlatform->quoteIdentifier($this->subject->table),
                $this->processExpression($constraint, $adapterPlatform),
            ];
        }

        return [$sqls];
    }

    /**
     * @param PlatformInterface|null $adapterPlatform
     * @return array
     */
    protected function processDropConstraints(PlatformInterface $adapterPlatform = null)
    {
        $sqls = [];
        foreach ($this->dropConstraints as $constraint) {
            $sqls[] = [
                $adapterPlatform->quoteIdentifier($this->subject->table),
                $adapterPlatform->quoteIdentifier($constraint)
            ];
        }

        return [$sqls];
    }


    /**
     * @param ExpressionInterface $expression
     * @param PlatformInterface $platform
     * @param DriverInterface|null $driver
     * @param ParameterContainer|null $parameterContainer
     * @param null $namedParameterPrefix
     * @return mixed|string
     */
    protected function processExpression(
        ExpressionInterface $expression,
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null,
        $namedParameterPrefix = null
    ) {
        $sql = $this->subject->processExpression($expression, $platform, $driver, $parameterContainer, $namedParameterPrefix);

        // alternatively add column decorators
        // varbinary data type without length parameter
        if ($expression instanceof Varbinary && preg_match('/VARBINARY(\s)*[^\(]/', $sql) === 1) {
            $sql = str_replace('VARBINARY', 'VARBINARY (max)', $sql);
        }

        return $sql;
    }

    /**
     * @param $optionValue
     * @return string
     */
    private function normalizeIdentityOptionValue($optionValue)
    {
        if (is_bool($optionValue)) {
            return '(1, 1)';
        }

        $optionValue = trim($optionValue);
        // if user did not use brackets for identity function parameters
        // add them.
        if (strpos($optionValue, '(') !== 0) {
            $optionValue = '(' . $optionValue . ')';
        }

        // end result should be (seed, increment)
        if (preg_match('/\([1-9]+\,(\s)*[1-9]+\)/', $optionValue) === 0) {
            throw new InvalidArgumentException('Identity format should be: (seed, increment). ' . $optionValue . ' is given instead.');
        }

        return $optionValue;
    }

    /**
     * @param string $name
     * @return string
     */
    private function normalizeColumnOption($name)
    {
        return strtolower(str_replace(['-', '_', ' '], '', $name));
    }

    /**
     * @param string $columnA
     * @param string $columnB
     * @return int
     */
    private function compareColumnOptions($columnA, $columnB)
    {
        $columnA = $this->normalizeColumnOption($columnA);
        $columnA = isset($this->columnOptionSortOrder[$columnA])
            ? $this->columnOptionSortOrder[$columnA] : count($this->columnOptionSortOrder);

        $columnB = $this->normalizeColumnOption($columnB);
        $columnB = isset($this->columnOptionSortOrder[$columnB])
            ? $this->columnOptionSortOrder[$columnB] : count($this->columnOptionSortOrder);

        return $columnA - $columnB;
    }
}
