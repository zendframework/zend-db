<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl\Constraint;

use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;

class CheckBuilder extends AbstractBuilder
{
    /**
     * {@inheritDoc}
     */
    protected $specification = 'CHECK (%s)';

    /**
     * @param \Zend\Db\Sql\Ddl\Constraint\Check $check
     * @param Context $context
     * @return array
     */
    public function getExpressionData($check, Context $context)
    {
        $this->validateSqlObject($check, 'Zend\Db\Sql\Ddl\Constraint\Check', __METHOD__);
        $values = [];
        $spec   = '';

        if ($check->getName()) {
            $spec .= $this->namedSpecification;
            $values[] = new ExpressionParameter($check->getName(), ExpressionInterface::TYPE_IDENTIFIER);
        }

        $values[] = new ExpressionParameter($check->getExpression(), ExpressionInterface::TYPE_LITERAL);

        return [[
            $spec . $this->specification,
            $values,
        ]];
    }
}
