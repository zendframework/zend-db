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
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;

class ColumnBuilder extends AbstractSqlBuilder
{
    protected $specification = '%s %s';

    /**
     * @param \Zend\Db\Sql\Ddl\Column\Column $column
     * @param Context $context
     * @return array
     */
    public function getExpressionData($column, Context $context)
    {
        $this->validateSqlObject($column, 'Zend\Db\Sql\Ddl\Column\Column', __METHOD__);
        $spec = $this->specification;

        $params   = [
            new ExpressionParameter($column->getName(), ExpressionInterface::TYPE_IDENTIFIER),
            new ExpressionParameter($column->getType(), ExpressionInterface::TYPE_LITERAL),
        ];

        if (!$column->isNullable()) {
            $spec .= ' NOT NULL';
        }

        if ($column->getDefault() !== null) {
            $spec    .= ' DEFAULT %s';
            $params[] = new ExpressionParameter($column->getDefault(), ExpressionInterface::TYPE_VALUE);
        }

        $data = [[
            $spec,
            $params,
        ]];

        foreach ($column->getConstraints() as $constraint) {
            $data[] = ' ';
            $data = array_merge(
                $data,
                $this->platformBuilder->getPlatformBuilder($constraint, $context)->getExpressionData($constraint, $context)
            );
        }

        return $data;
    }
}
