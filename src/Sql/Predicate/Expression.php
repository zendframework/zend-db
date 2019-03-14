<?php

declare(strict_types=1);

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Expression as BaseExpression;
use function array_slice;
use function func_get_args;

class Expression extends BaseExpression implements PredicateInterface
{
    /**
     * Constructor
     *
     * @param string $expression
     * @param int|float|bool|string|array $valueParameter
     */
    public function __construct($expression = null, $valueParameter = null)
    {
        if ($expression) {
            $this->setExpression($expression);
        }

        $this->setParameters(is_array($valueParameter) ? $valueParameter : array_slice(func_get_args(), 1));
    }
}
