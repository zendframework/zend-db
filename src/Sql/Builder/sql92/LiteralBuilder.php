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
use Zend\Db\Sql\Builder\Context;

class LiteralBuilder extends AbstractSqlBuilder
{
    /**
     * @param \Zend\Db\Sql\Literal $expression
     * @param Context $context
     * @return array
     */
    protected function build($expression, Context $context)
    {
        $this->validateSqlObject($expression, 'Zend\Db\Sql\Literal', __METHOD__);
        return [
            'spec' => $expression->getLiteral()
        ];
    }
}
