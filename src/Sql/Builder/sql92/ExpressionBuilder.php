<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Builder\Context;

class ExpressionBuilder extends AbstractSqlBuilder
{
    /**
     * @param Expression $expression
     * @param Context $context
     * @return array
     * @throws Exception\RuntimeException
     */
    public function getExpressionData($expression, Context $context)
    {
        $this->validateSqlObject($expression, 'Zend\Db\Sql\Expression', __METHOD__);
        $parameters = (is_scalar($expression->getParameters())) ? [$expression->getParameters()] : $expression->getParameters();
        $parametersCount = count($parameters);
        $expression = str_replace('%', '%%', $expression->getExpression());

        if ($parametersCount == 0) {
            return [
                str_ireplace(Expression::PLACEHOLDER, '', $expression)
            ];
        }

        // assign locally, escaping % signs
        $expression = str_replace(Expression::PLACEHOLDER, '%s', $expression, $count);
        // test number of replacements without considering same variable begin used many times first, which is
        // faster, if the test fails then resort to regex wich are slow and used rarely
        if ($count !== $parametersCount && $parametersCount === preg_match_all('/\:[a-zA-Z0-9_]*/', $expression)) {
            throw new Exception\RuntimeException('The number of replacements in the expression does not match the number of parameters');
        }
        return [[
            $expression,
            $parameters,
        ]];
    }
}
