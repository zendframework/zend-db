<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\TestAsset;

use Zend\Db\Sql\Builder\Context;

class ExpressionBuilder extends \Zend\Db\Sql\Builder\sql92\ExpressionBuilder
{
    public function getExpressionData($expression, Context $context)
    {
        $expressionString = $expression->getExpression();
        $expression->setExpression('{decorate-' . $expressionString . '-decorate}');
        $result = parent::getExpressionData($expression, $context);
        $expression->setExpression($expressionString);
        return $result;
    }
}
