<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl\Constraint;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;

class AbstractBuilder extends AbstractSqlBuilder
{
    /**
     * @var string
     */
    protected $columnSpecification = ' (%s)';

    /**
     * @var string
     */
    protected $namedSpecification = 'CONSTRAINT %s ';

    /**
     * @var string
     */
    protected $specification = '';

    /**
     * @param \Zend\Db\Sql\Ddl\Constraint\ConstraintInterface $constraint
     * @param Context $context
     * @return array
     */
    public function getExpressionData($constraint, Context $context)
    {
        $this->validateSqlObject($constraint, 'Zend\Db\Sql\Ddl\Constraint\ConstraintInterface', __METHOD__);
        $parameters = [];
        $spec = '';

        if ($constraint->getName()) {
            $spec .= $this->namedSpecification;
            $parameters[] = new ExpressionParameter($constraint->getName(), ExpressionInterface::TYPE_IDENTIFIER);
        }

        $spec .= $this->specification;

        if ($constraint->getColumns()) {
            foreach ($constraint->getColumns() as $column) {
                $parameters[] = new ExpressionParameter($column, ExpressionInterface::TYPE_IDENTIFIER);
            }
            $spec .= sprintf(
                $this->columnSpecification,
                rtrim(str_repeat('%s, ', count($constraint->getColumns())), ', ')
            );
        }

        return [[
            $spec,
            $parameters,
        ]];
    }
}
