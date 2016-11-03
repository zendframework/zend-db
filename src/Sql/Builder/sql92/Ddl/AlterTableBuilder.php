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
    protected $tableSpecification = "ALTER TABLE %1\$s\n";
    protected $addColumnsSpecification = [
        'forEach' => "ADD COLUMN %1\$s,\n",
        'implode' => " ",
    ];
    protected $changeColumnsSpecification = [
        'forEach' => "CHANGE COLUMN %1\$s %2\$s,\n",
        'implode' => "",
    ];
    protected $dropColumnsSpecification = [
        'forEach' => "DROP COLUMN %1\$s,\n",
        'implode' => "",
    ];
    protected $addConstraintsSpecification = [
        'forEach' => "ADD %1\$s,\n",
        'implode' => "",
    ];
    protected $dropConstraintsSpecification = [
        'forEach' => "DROP CONSTRAINT %1\$s,\n",
        'implode' => "",
    ];

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    public function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Ddl\AlterTable', __METHOD__);
        return [
            $this->build_Table($sqlObject, $context),
            $this->build_AddColumns($sqlObject, $context),
            $this->build_ChangeColumns($sqlObject, $context),
            $this->build_DropColumns($sqlObject, $context),
            $this->build_AddConstraints($sqlObject, $context),
            $this->build_DropConstraints($sqlObject, $context),
        ];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Table(AlterTable $sqlObject, Context $context)
    {
        return [
            'spec' => $this->tableSpecification,
            'params' => [
                $sqlObject->table,
            ],
        ];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_AddColumns(AlterTable $sqlObject, Context $context)
    {
        return [
            'spec' => $this->addColumnsSpecification,
            'params' => $sqlObject->addColumns,
        ];
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
                $column
            ];
        }
        return [
            'spec' => $this->changeColumnsSpecification,
            'params' => $sqls,
        ];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_DropColumns(AlterTable $sqlObject, Context $context)
    {
        $columns = [];
        foreach ($sqlObject->dropColumns as $column) {
            $columns[] = $context->getPlatform()->quoteIdentifier($column);
        }
        return [
            'spec' => $this->dropColumnsSpecification,
            'params' => $columns,
        ];
    }

    /**
     * @param AlterTable $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_AddConstraints(AlterTable $sqlObject, Context $context)
    {
        return [
            'spec' => $this->addConstraintsSpecification,
            'params' => $sqlObject->addConstraints,
        ];
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
        return [
            'spec' => $this->dropConstraintsSpecification,
            'params' => $sqls,
        ];
    }
}
