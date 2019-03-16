<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\AbstractExpression;

class Like extends AbstractExpression implements PredicateInterface
{
    /** @var string */
    protected $specification = '%1$s LIKE %2$s';

    /** @var string */
    protected $identifier = '';

    /** @var string */
    protected $like = '';

    public function __construct(?string $identifier = null, ?string $like = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($like) {
            $this->setLike($like);
        }
    }

    public function setIdentifier(string $identifier) : self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    public function setLike(string $like) : self
    {
        $this->like = $like;

        return $this;
    }

    public function getLike() : string
    {
        return $this->like;
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
        [$values[], $types[]] = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        [$values[], $types[]] = $this->normalizeArgument($this->like, self::TYPE_VALUE);
        return [
            [
                $this->specification,
                $values,
                $types,
            ]
        ];
    }
}
