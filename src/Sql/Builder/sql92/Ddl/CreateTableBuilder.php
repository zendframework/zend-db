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
use Zend\Db\Sql\Ddl\CreateTable;

class CreateTableBuilder extends AbstractSqlBuilder
{
    const SPECIFICATION_TABLE       = 'table';
    const SPECIFICATION_COLUMNS     = 'columns';
    const SPECIFICATION_CONSTRAINTS = 'constraints';

    protected $specifications = [
        self::SPECIFICATION_TABLE => 'CREATE %1$sTABLE %2$s (',
        self::SPECIFICATION_COLUMNS  => [
            "\n    %1\$s" => [
                [1 => '%1$s', 'combinedby' => ",\n    "]
            ]
        ],
        'combinedBy' => ",",
        self::SPECIFICATION_CONSTRAINTS => [
            "\n    %1\$s" => [
                [1 => '%1$s', 'combinedby' => ",\n    "]
            ]
        ],
        'statementEnd' => '%1$s',
    ];

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Table(CreateTable $sqlObject, Context $context)
    {
        return [
            $sqlObject->isTemporary ? 'TEMPORARY ' : '',
            $context->getPlatform()->quoteIdentifier($sqlObject->table),
        ];
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Columns(CreateTable $sqlObject, Context $context)
    {
        $COLUMNS = $sqlObject->columns;
        if (! $COLUMNS) {
            return;
        }

        $sqls = [];

        foreach ($COLUMNS as $column) {
            $sqls[] = $this->buildSqlString($column, $context);
        }

        return [$sqls];
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return string
     */
    protected function build_Combinedby(CreateTable $sqlObject, Context $context)
    {
        if ($sqlObject->constraints && $sqlObject->columns) {
            return $this->specifications['combinedBy'];
        }
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return null|array
     */
    protected function build_Constraints(CreateTable $sqlObject, Context $context)
    {
        $CONSTRAINTS = $sqlObject->constraints;
        if (!$CONSTRAINTS) {
            return;
        }

        $sqls = [];

        foreach ($CONSTRAINTS as $constraint) {
            $sqls[] = $this->buildSqlString($constraint, $context);
        }

        return [$sqls];
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return string
     */
    protected function build_StatementEnd(CreateTable $sqlObject, Context $context)
    {
        return ["\n)"];
    }
}
