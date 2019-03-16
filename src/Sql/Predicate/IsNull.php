<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\AbstractExpression;

class IsNull extends AbstractExpression implements PredicateInterface
{
    /** @var string */
    protected $specification = '%1$s IS NULL';

    /** @var null|string */
    protected $identifier;

    public function __construct(?string $identifier = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
    }

    public function setIdentifier(string $identifier) : self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier() : ?string
    {
        return $this->identifier;
    }

    public function setSpecification(string $specification) : self
    {
        $this->specification = $specification;

        return $this;
    }

    public function getSpecification() : string
    {
        return $this->specification;
    }

    public function getExpressionData() : array
    {
        $identifier = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        return [[
            $this->getSpecification(),
            [$identifier[0]],
            [$identifier[1]],
        ]];
    }
}
