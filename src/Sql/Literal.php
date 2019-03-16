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
    protected $literal;

    public function __construct(string $literal = '')
    {
        $this->literal = $literal;
    }

    public function setLiteral(string $literal) : self
    {
        $this->literal = $literal;
        return $this;
    }

    public function getLiteral() : string
    {
        return $this->literal;
    }

    public function getExpressionData() : array
    {
        return [[
            str_replace('%', '%%', $this->literal),
            [],
            []
        ]];
    }
}
