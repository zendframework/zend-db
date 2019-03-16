<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

class Literal implements ExpressionInterface
{
    /** @var string */
    protected $literal = '';

    /**
     * @param $literal
     */
    public function __construct($literal = '')
    {
        $this->literal = $literal;
    }

    /**
     * @param string $literal
     * @return self Provides a fluent interface
     */
    public function setLiteral($literal) : self
    {
        $this->literal = $literal;
        return $this;
    }

    /**
     * @return string
     */
    public function getLiteral() : string
    {
        return $this->literal;
    }

    /**
     * @return array
     */
    public function getExpressionData() : array
    {
        return [[
            str_replace('%', '%%', $this->literal),
            [],
            []
        ]];
    }
}
