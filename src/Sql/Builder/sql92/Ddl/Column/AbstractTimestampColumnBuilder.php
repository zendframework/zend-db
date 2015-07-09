<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl\Column;

use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;

class AbstractTimestampColumnBuilder extends ColumnBuilder
{
    public function build($column, Context $context)
    {
        $this->validateSqlObject($column, 'Zend\Db\Sql\Ddl\Column\AbstractTimestampColumn', __METHOD__);
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

        $options = $column->getOptions();

        if (isset($options['on_update'])) {
            $spec    .= ' %s';
            $params[] = new ExpressionParameter('ON UPDATE CURRENT_TIMESTAMP', ExpressionInterface::TYPE_LITERAL);
        }

        $data = [[
            'spec' => $spec,
            'params' => $params,
        ]];

        foreach ($column->getConstraints() as $constraint) {
            $data[] = ' ';
            $data = array_merge($data, $this->platformBuilder->getPlatformBuilder($constraint, $context)->getExpressionData($constraint, $context));
        }

        return $data;
    }
}
