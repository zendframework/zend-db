<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl\Column;

abstract class AbstractLengthColumn extends Column
{
    /** @var int */
    protected $length;

    public function __construct(
        ?string $name = null,
        ?int $length = null,
        bool $nullable = false,
        $default = null,
        array $options = []
    ) {
        $this->setLength($length);

        parent::__construct($name, $nullable, $default, $options);
    }

    public function setLength(int $length) : self
    {
        $this->length = $length;

        return $this;
    }

    public function getLength() : int
    {
        return $this->length;
    }

    protected function getLengthExpression() : string
    {
        return (string) $this->length;
    }

    public function getExpressionData() : array
    {
        $data = parent::getExpressionData();

        if ($this->getLengthExpression()) {
            $data[0][1][1] .= '(' . $this->getLengthExpression() . ')';
        }

        return $data;
    }
}
