<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;
use Zend\Db\Sql\SelectableInterface;

class InsertBuilder extends AbstractSqlBuilder
{
    protected $specificationTable = 'INSERT INTO %1$s';
    protected $specificationColumns = [
        'implode' => ', ',
        'format' => '(%s)',
    ];
    protected $specificationValues = [
        'implode' => ', ',
        'format' => 'VALUES (%s)',
    ];
    protected $specificationValuesMultiple = [
        'forEach' => [
            'implode' => ', ',
            'format' => '(%s)',
        ],
        'implode' => ', ',
        'format' => 'VALUES %s',
    ];

    /**
     * @param Insert $sqlObject
     * @param Context $context
     * @return array
     */
    public function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Insert', __METHOD__);
        return [
            $this->build_Table($sqlObject, $context),
            $this->build_Columns($sqlObject, $context),
            $this->build_Values($sqlObject, $context),
        ];
    }

    protected function build_Table(Insert $sqlObject, Context $context)
    {
        return [
            'spec' => $this->specificationTable,
            'params' => $sqlObject->table,
        ];
    }

    protected function build_Columns(Insert $sqlObject, Context $context)
    {
        if (!$sqlObject->columns) {
            return;
        }
        return [
            'spec' => $this->specificationColumns,
            'params' => array_map([$context->getPlatform(), 'quoteIdentifier'], $sqlObject->columns),
        ];
    }

    protected function build_Values(Insert $sqlObject, Context $context)
    {
        $values  = $sqlObject->values;

        if ($values instanceof SelectableInterface) {
            return $values;
        }

        if (!is_array(reset($values))) {
            $pValues  = [];
            if ($sqlObject->columns) {
                foreach (array_combine($sqlObject->columns, $values) as $column=>$value) {
                    list(, $pValues[]) = $this->resolveColumnValue($column, $value, $context);
                }
            } else {
                foreach ($values as $value) {
                    list(, $pValues[]) = $this->resolveColumnValue(null, $value, $context);
                }
            }
            return [
                'spec' => $this->specificationValues,
                'params' => $pValues,
            ];
        }

        foreach ($values as &$valueRow) {
            foreach ($valueRow as &$value) {
                list(, $value) = $this->resolveColumnValue(null, $value, $context);
            }
        }
        return [
            'spec' => $this->specificationValuesMultiple,
            'params' => $values,
        ];
    }
}
