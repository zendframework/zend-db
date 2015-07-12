<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl\Column;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Builder\Context;

class ColumnBuilder extends AbstractSqlBuilder
{
    protected $specifications = [
        'name' => [
            'spec'             => '%s',
            'propertyType'     => ExpressionInterface::TYPE_IDENTIFIER,
        ],
        'type' => [
            'spec'             => [
                'byCount' => [
                    1 => '%1$s',
                    2 => '%1$s(%2$s)',
                    3 => '%1$s(%2$s,%3$s)'
                ],
            ],
            'subProperties'    => [
                'type'    => null,
                'length'  => null,
                'decimal' => null,
            ],
        ],
        'nullable' => [
            'valueMap' => [false => 'NOT NULL', true => null],
        ],
        'constraints' => [
            'spec' => [
                'implode' => ' ',
            ],
        ],
        'default' => [
            'spec'             => 'DEFAULT %s',
            'propertyType'     => ExpressionInterface::TYPE_VALUE,
        ],
    ];

    /**
     * @param \Zend\Db\Sql\Ddl\Column\Column $column
     * @param Context $context
     * @return array
     */
    protected function build($column, Context $context)
    {
        $this->validateSqlObject($column, 'Zend\Db\Sql\Ddl\Column\Column', __METHOD__);
        $data = $this->buildColumnSpec($context, $this->specifications, $column);
        $data[$this->implodeGlueKey] = ' ';
        return $data;
    }

    /**
     * @param Context $context
     * @param array $description
     * @param \Zend\Db\Sql\Ddl\Column\Column $column
     * @param array $options
     * @return array
     */
    protected function buildColumnSpec(Context $context, $description, $column, $options = null)
    {
        if ($options === null) {
            $options = [];
            foreach ($column->getOptions() as $k=>$v) {
                $options[strtolower(str_replace(['-', '_', ' '], '', $k))] = $v;
            }
        }
        foreach ($description as $key => $spec) {
            $value = null;
            $spec  = is_string($spec) ? ['spec' => $spec] : $spec;
            if (isset($spec['subProperties'])) {
                $value = $this->buildColumnSpec($context, $spec['subProperties'], $column, $options);
            } else {
                $methodGet   = 'get' . ucfirst($key);
                $methodIs    = 'is' . ucfirst($key);
                if (method_exists($column, $methodGet)) {
                    $value = $column->$methodGet();
                } elseif (method_exists($column, $methodIs)) {
                    $value = $column->$methodIs();
                } elseif (array_key_exists($key, $options)) {
                    $value = $options[$key];
                }
            }
            if ($value === null || $value === '') {
                unset($description[$key]);
                continue;
            }
            if (isset($spec['valueMap'])) {
                $valueMapKey = is_bool($value)
                    ? (int)$value
                    : $value;
                $value = array_key_exists($valueMapKey, $spec['valueMap'])
                    ? $spec['valueMap'][$valueMapKey]
                    : null;
            }

            if ($value == null || $value === '') {
                unset($description[$key]);
                continue;
            }
            if (isset($spec['propertyType'])) {
                switch ($spec['propertyType']) {
                    case ExpressionInterface::TYPE_IDENTIFIER :
                        $value = $context->getPlatform()->quoteIdentifier($value);
                        break;
                    case ExpressionInterface::TYPE_VALUE :
                        $value = $context->getPlatform()->quoteValue($value);
                        break;
                }
            }
            $description[$key] = isset($spec['spec'])
                    ? ['spec' => $spec['spec'], 'params' => $value]
                    : $value;
        }
        return $description;
    }
}
