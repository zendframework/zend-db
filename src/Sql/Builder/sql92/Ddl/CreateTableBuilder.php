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
    protected $tableSpecification = 'CREATE %1$sTABLE %2$s (';
    protected $columnsSpecification = [
        'implode' => [
            'prefix' => "\n    ",
            'glue' => ",\n    ",
        ],
    ];
    protected $combinedBySpecification = ",\n    ";
    protected $constraintsSpecification = [
        'implode' => ",\n    ",
    ];
    protected $statementEndSpecification = "\n)";

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Ddl\CreateTable', __METHOD__);
        return [
            $this->build_Table($sqlObject, $context),
            $this->build_Columns($sqlObject, $context),
            $this->build_Combinedby($sqlObject, $context),
            $this->build_Constraints($sqlObject, $context),
            $this->build_StatementEnd($sqlObject, $context),
        ];
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Table(CreateTable $sqlObject, Context $context)
    {
        return [
            'spec' => $this->tableSpecification,
            'params' => [
                $sqlObject->isTemporary ? 'TEMPORARY ' : '',
                $sqlObject->table,
            ],
        ];
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Columns(CreateTable $sqlObject, Context $context)
    {
        if (! $sqlObject->columns) {
            return;
        }
        return [
            'spec' => $this->columnsSpecification,
            'params' => $sqlObject->columns,
        ];
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return string
     */
    protected function build_Combinedby(CreateTable $sqlObject, Context $context)
    {
        if ($sqlObject->constraints && $sqlObject->columns) {
            return $this->combinedBySpecification;
        }
        if ($sqlObject->constraints && !$sqlObject->columns) {
            return "\n   ";
        }
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return null|array
     */
    protected function build_Constraints(CreateTable $sqlObject, Context $context)
    {
        if (!$sqlObject->constraints) {
            return;
        }

        return [
            'spec' => $this->constraintsSpecification,
            'params' => $sqlObject->constraints,
        ];
    }

    /**
     * @param CreateTable $sqlObject
     * @param Context $context
     * @return string
     */
    protected function build_StatementEnd(CreateTable $sqlObject, Context $context)
    {
        return $this->statementEndSpecification;
    }
}
