<?php
/**
 * @see       http://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Db\Sql\Platform\SqlServer\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Exception\InvalidArgumentException;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class CreateTableDecorator extends CreateTable implements PlatformDecoratorInterface
{
    /**
     * @var CreateTable
     */
    protected $subject;

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
     * @param CreateTable $subject
     * @return self Provides a fluent interface
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

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
                        $insertStart[2] = ! isset($insertStart[2]) ? $insertAt : $insertStart[2];
                    // no break
                    case 'PRIMARY':
                    case 'UNIQUE':
                        $insertStart[1] = ! isset($insertStart[1]) ? $insertAt : $insertStart[1];
                    // no break
                    default:
                        $insertStart[0] = ! isset($insertStart[0]) ? $insertAt : $insertStart[0];
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
    protected function processColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = [];
        /**
         * @var Column $column
         */
        foreach ($this->columns as $column) {
            $sql           = $this->processExpression($column, $adapterPlatform);
            $optionOffsets = $this->getSqlInsertOffsets($sql);
            $columnOptions = $column->getOptions();

            uksort($columnOptions, [$this, 'compareColumnOptions']);

            foreach ($columnOptions as $optionName => $optionValue) {
                $insert = '';

                if (! $optionValue) {
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
            $sqls[] = $sql;
        }

        return [$sqls];
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
            throw new InvalidArgumentException(
                'Identity format should be: (seed, increment). ' . $optionValue . ' is given instead.'
            );
        }

        return $optionValue;
    }

    /**
     * @param PlatformInterface $adapterPlatform
     * @return array
     */
    protected function processTable(PlatformInterface $adapterPlatform = null)
    {
        $table = ($this->isTemporary ? '#' : '') . ltrim($this->table, '#');
        return [
            '',
            $adapterPlatform->quoteIdentifier($table),
        ];
    }
}
