<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Expression as BaseExpression;
use function array_slice;
use function func_get_args;
use function is_array;

class Expression extends BaseExpression implements PredicateInterface
{
    public function __construct(?string $expression = null, $valueParameter = null)
    {
        if ($expression) {
            $this->setExpression($expression);
        }

        $this->setParameters(is_array($valueParameter) ? $valueParameter : array_slice(func_get_args(), 1));
    }
}
