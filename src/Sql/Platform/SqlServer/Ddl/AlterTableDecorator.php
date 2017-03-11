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
use Zend\Db\Sql\Ddl\Column\Varbinary;
use Zend\Db\Sql\Exception\InvalidArgumentException;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class AlterTableDecorator extends AlterTable implements PlatformDecoratorInterface
{
    /**
     * @var AlterTable
     */
    protected $subject;

    protected $alterSpecifications = [
        self::ADD_COLUMNS  => [
            "%1\$s" => [
                [2 => "ALTER TABLE %1\$s\n ADD %2\$s;", 'combinedby' => "\n"],
            ],
        ],
        self::CHANGE_COLUMNS  => [
            "%1\$s" => [
                [2 => "CHANGE COLUMN %1\$s %2\$s,\n", 'combinedby' => ''],
            ],
        ],
        self::DROP_COLUMNS  => [
            "%1\$s" => [
                [1 => "DROP COLUMN %1\$s,\n", 'combinedby' => ''],
            ],
        ],
        self::ADD_CONSTRAINTS  => [
            "%1\$s" => [
                [2 => "ALTER TABLE %1\$s\n ADD %2\$s;", 'combinedby' => "\n"],
            ],
        ],
        self::DROP_CONSTRAINTS  => [
            "%1\$s" => [
                [1 => "DROP CONSTRAINT %1\$s,\n", 'combinedby' => ''],
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
        $this->specifications = array_merge($this->specifications, $this->alterSpecifications);
        unset($this->specifications[self::TABLE]);
        $this->subject->specifications = $this->specifications;

        return $this;
    }

    // SqlServer cannot have multiple operations in ALTER TABLE
    // generate on first time, and reuse for all operations


    /**
     * @param string $sql
     * @return array
     */
    protected function getSqlInsertOffsets($sql)
    {
        $sqlLength   = strlen($sql);
        $insertStart = [];

        foreach (['NOT NULL', 'NULL', 'DEFAULT', 'UNIQUE', 'PRIMARY', 'REFERENCES'] as $needle) {
            $insertPos = strpos($sql, ' '.$needle);

            if ($insertPos !== false) {
                switch ($needle) {
                    case 'REFERENCES':
                        $insertStart[2] = !isset($insertStart[2]) ? $insertPos : $insertStart[2];
                    // no break
                    case 'PRIMARY':
                    case 'UNIQUE':
                        $insertStart[1] = !isset($insertStart[1]) ? $insertPos : $insertStart[1];
                    // no break
                    default:
                        $insertStart[0] = !isset($insertStart[0]) ? $insertPos : $insertStart[0];
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
         * @var int
         * @var Column
         */
        foreach ($this->addColumns as $i => $column) {
            $sql           = $this->processExpression($column, $adapterPlatform);
            $insertStart   = $this->getSqlInsertOffsets($sql);
            $columnOptions = $column->getOptions();

            uksort($columnOptions, [$this, 'compareColumnOptions']);

            foreach ($columnOptions as $coName => $coValue) {
                $insert = '';

                if (!$coValue) {
                    continue;
                }

                switch ($this->normalizeColumnOption($coName)) {
                    case 'filestream':
                        $insert = ' FILESTREAM';
                        $j = 0;
                        break;
                    case 'collate':
                        $insert = ' COLLATE '.$adapterPlatform->quoteIdentifier($coValue);
                        $j = 0;
                        break;
                    case 'identity':
                    case 'serial':
                    case 'autoincrement':
                        $insert = ' IDENTITY '.$this->normalizeIdentityOptionValue($coValue);
                        $j = 0;
                        break;
                    case 'rowguidcol':
                        $insert = ' ROWGUIDCOL';
                        $j = 1;
                        break;
                    case 'sparse':
                        $insert = ' SPARSE';
                        $j = 1;
                        break;
                    case 'encryptedwith':
                        $insert = ' ENCRYPTED WITH '.$coValue;
                        $j = 1;
                        break;
                    case 'maskedwith':
                        $insert = ' MASKED WITH '.$coValue;
                        $j = 1;
                        break;
                    case 'comment':
                        $insert = ' COMMENT '.$adapterPlatform->quoteValue($coValue);
                        $j = 2;
                        break;
                }

                if ($insert) {
                    $j = isset($j) ? $j : 0;
                    $sql = substr_replace($sql, $insert, $insertStart[$j], 0);
                    $insertStartCount = count($insertStart);
                    for (; $j < $insertStartCount; ++$j) {
                        $insertStart[$j] += strlen($insert);
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
     * @param $value
     * @return string
     */
    private function normalizeIdentityOptionValue($value)
    {
        if (is_bool($value)) {
            return '(1, 1)';
        }

        $value = trim($value);
        // if user did not use brackets for identity function parameters
        // add them.
        if (strpos($value, '(') !== 0) {
            $value = '('.$value.')';
        }

        // end result should be (seed, increment)
        if (preg_match('/\([1-9]+\,(\s)*[1-9]+\)/', $value) === 0) {
            throw new InvalidArgumentException('Identity format should be: (seed, increment). '.$value.' is given instead.');
        }

        return $value;
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
