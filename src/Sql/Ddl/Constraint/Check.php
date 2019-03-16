<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl\Constraint;

use Zend\Db\Sql\ExpressionInterface;

class Check extends AbstractConstraint
{
    /** @var string|ExpressionInterface */
    protected $expression;

    /**
     * {@inheritDoc}
     */
    protected $specification = 'CHECK (%s)';

    /**
     * @param string|ExpressionInterface $expression
     * @param string $name
     */
    public function __construct($expression, string $name = '')
    {
        $this->expression = $expression;
        $this->name       = $name;
    }

    public function getExpressionData() : array
    {
        $newSpecTypes = [self::TYPE_LITERAL];
        $values       = [$this->expression];
        $newSpec      = '';

        if ($this->name) {
            $newSpec .= $this->namedSpecification;

            array_unshift($values, $this->name);
            array_unshift($newSpecTypes, self::TYPE_IDENTIFIER);
        }

        return [[
            $newSpec . $this->specification,
            $values,
            $newSpecTypes,
        ]];
    }
}
