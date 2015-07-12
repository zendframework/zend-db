<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class InsertBuilder extends AbstractSqlBuilder
{
    /**
     * @var array Specification array
     */
    protected $insertSpecification = [
        'byArgNumber' => [
            2 => ['implode' => ', '],
            3 => ['implode' => ', '],
        ],
        'format' => 'INSERT INTO %1$s (%2$s) VALUES (%3$s)',
    ];
    protected $selectSpecification = [
        'byCount' => [
            2 => [
                'format' => 'INSERT INTO %1$s %2$s'
            ],
            3 => [
                'byArgNumber' => [
                    2 => ['implode' => ', '],
                ],
                'format' => 'INSERT INTO %1$s (%2$s) %3$s'
            ],
        ],
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
            $this->build_Insert($sqlObject, $context),
            $this->build_Select($sqlObject, $context),
        ];
    }

    /**
     * @param Insert $sqlObject
     * @param Context $context
     * @return null|array
     * @throws Exception\InvalidArgumentException
     */
    protected function build_Insert(Insert $sqlObject, Context $context)
    {
        if ($sqlObject->select) {
            return;
        }

        if (!$sqlObject->columns) {
            throw new Exception\InvalidArgumentException('values or select should be present');
        }

        $columns = [];
        $values  = [];
        foreach (array_combine($sqlObject->columns, $sqlObject->values) as $column=>$value) {
            list($columns[], $values[]) = $this->resolveColumnValue($column, $value, $context);
        }

        return [
            'spec' => $this->insertSpecification,
            'params' => [
                $sqlObject->table,
                $columns,
                $values,
            ],
        ];
    }

    /**
     * @param Insert $sqlObject
     * @param Context $context
     * @return null|array
     */
    protected function build_Select(Insert $sqlObject, Context $context)
    {
        if (!$sqlObject->select) {
            return;
        }

        if ($sqlObject->columns) {
            return [
                'spec'   => $this->selectSpecification,
                'params' => [
                    $sqlObject->table,
                    array_map([$context->getPlatform(), 'quoteIdentifier'], $sqlObject->columns),
                    $sqlObject->select
                ],
            ];
        }
        return [
            'spec'   => $this->selectSpecification,
            'params' => [
                $sqlObject->table,
                $sqlObject->select
            ],
        ];
    }
}
