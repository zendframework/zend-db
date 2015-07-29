<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Sql\Update;
use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class UpdateBuilder extends AbstractSqlBuilder
{
    protected $updateSpecification = 'UPDATE %1$s';
    protected $whereSpecification = 'WHERE %1$s';
    /*protected $joinsSpecification = [
        '%1$s' => [
            [3 => '%1$s JOIN %2$s ON %3$s', 'combinedby' => ' ']
        ]
    ];*/
    protected $joinsSpecification = [
        'forEach' => [
        'byArgNumber' => [
            2 => [
                    'byCount' => [
                        1 => '%1$s', 2 => '%1$s AS %2$s'
                    ],
                ],
            ],
            'format' => '%1$s JOIN %2$s ON %3$s',
        ],
        'implode' => ' ',
    ];
    protected $setSpecification = [
        'byArgNumber' => [
            1 => [
                'forEach' => '%1$s = %2$s',
                'implode' => ', ',
            ],
        ],
        'format' => 'SET %1$s',
    ];

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return array
     */
    public function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Update', __METHOD__);
        return [
            $this->build_Update($sqlObject, $context),
            $this->build_Joins($sqlObject, $context),
            $this->build_Set($sqlObject, $context),
            $this->build_Where($sqlObject, $context),
        ];
    }

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Update(Update $sqlObject, Context $context)
    {
        return [
            'spec' => $this->updateSpecification,
            'params' => $sqlObject->table,
        ];
    }

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return string
     */
    protected function build_Set(Update $sqlObject, Context $context)
    {
        $setSql = [];
        foreach ($sqlObject->set as $column => $value) {
            if (is_scalar($value) && $context->getParameterContainer()) {
                $context->getParameterContainer()->offsetSet($column, $value);
                $value = $context->getDriver()->formatParameterName($column);
            } else {
                $value = $this->resolveColumnValue($value, $context);
            }
            $setSql[] = [
                $context->getPlatform()->quoteIdentifier($column),
                $value
            ];
        }
        return [
            'spec' => $this->setSpecification,
            'params' => [
                $setSql
            ],
        ];
    }

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Where(Update $sqlObject, Context $context)
    {
        if ($sqlObject->where->count() == 0) {
            return;
        }
        return [
            'spec' => $this->whereSpecification,
            'params' => $sqlObject->where
        ];
    }
}
