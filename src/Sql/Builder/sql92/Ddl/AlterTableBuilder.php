<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;
use Zend\Db\Sql\Ddl\AlterTable;

class AlterTableBuilder extends AbstractSqlBuilder
{
    const SPECIFICATION_TABLE            = 'table';
    const SPECIFICATION_ADD_COLUMNS      = 'addColumns';
    const SPECIFICATION_CHANGE_COLUMNS   = 'changeColumns';
    const SPECIFICATION_DROP_COLUMNS     = 'dropColumns';
    const SPECIFICATION_ADD_CONSTRAINTS  = 'addConstraints';
    const SPECIFICATION_DROP_CONSTRAINTS = 'dropConstraints';

    protected $specifications = [
        self::SPECIFICATION_TABLE => "ALTER TABLE %1\$s\n",
        self::SPECIFICATION_ADD_COLUMNS  => [
            "%1\$s" => [
                [1 => "ADD COLUMN %1\$s,\n", 'combinedby' => ""]
            ]
        ],
        self::SPECIFICATION_CHANGE_COLUMNS  => [
            "%1\$s" => [
                [2 => "CHANGE COLUMN %1\$s %2\$s,\n", 'combinedby' => ""],
            ]
        ],
        self::SPECIFICATION_DROP_COLUMNS  => [
            "%1\$s" => [
                [1 => "DROP COLUMN %1\$s,\n", 'combinedby' => ""],
            ]
        ],
        self::SPECIFICATION_ADD_CONSTRAINTS  => [
            "%1\$s" => [
                [1 => "ADD %1\$s,\n", 'combinedby' => ""],
            ]
        ],
        self::SPECIFICATION_DROP_CONSTRAINTS  => [
            "%1\$s" => [
                [1 => "DROP CONSTRAINT %1\$s,\n", 'combinedby' => ""],
            ]
        ]
    ];

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Table(AlterTable $sqlObject, Context $context)
    {
        return [$context->getPlatform()->quoteIdentifier($sqlObject->table)];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_AddColumns(AlterTable $sqlObject, Context $context)
    {
        $sqls = [];
        foreach ($sqlObject->addColumns as $column) {
            $sqls[] = $this->buildSqlString($column, $context);
        }

        return [$sqls];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_ChangeColumns(AlterTable $sqlObject, Context $context)
    {
        $sqls = [];
        foreach ($sqlObject->changeColumns as $name => $column) {
            $sqls[] = [
                $context->getPlatform()->quoteIdentifier($name),
                $this->buildSqlString($column, $context)
            ];
        }

        return [$sqls];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_DropColumns(AlterTable $sqlObject, Context $context)
    {
        $sqls = [];
        foreach ($sqlObject->dropColumns as $column) {
            $sqls[] = $context->getPlatform()->quoteIdentifier($column);
        }

        return [$sqls];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_AddConstraints(AlterTable $sqlObject, Context $context)
    {
        $sqls = [];
        foreach ($sqlObject->addConstraints as $constraint) {
            $sqls[] = $this->buildSqlString($constraint, $context);
        }

        return [$sqls];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_DropConstraints(AlterTable $sqlObject, Context $context)
    {
        $sqls = [];
        foreach ($sqlObject->dropConstraints as $constraint) {
            $sqls[] = $context->getPlatform()->quoteIdentifier($constraint);
        }

        return [$sqls];
    }
}
