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

use Zend\Db\Sql\AbstractExpression;

class Like extends AbstractExpression implements PredicateInterface
{
    /**
     * @var string
     */
    protected $specification = '%1$s LIKE %2$s';

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var string
     */
    protected $like = '';

    /**
     * @param string $identifier
     * @param string $like
     */
    public function __construct($identifier = null, $like = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($like) {
            $this->setLike($like);
        }
    }

    /**
     * @param  string $identifier
     *
     * @return self Provides a fluent interface
     */
    public function setIdentifier(string $identifier) : self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    /**
     * @param  string $like
     *
     * @return self Provides a fluent interface
     */
    public function setLike(string $like) : self
    {
        $this->like = $like;

        return $this;
    }

    /**
     * @return string
     */
    public function getLike() : string
    {
        return $this->like;
    }

    /**
     * @param  string $specification
     *
     * @return self Provides a fluent interface
     */
    public function setSpecification(string $specification) : self
    {
        $this->specification = $specification;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpecification() : string
    {
        return $this->specification;
    }

    /**
     * @return array
     */
    public function getExpressionData() : array
    {
        list($values[], $types[]) = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        list($values[], $types[]) = $this->normalizeArgument($this->like, self::TYPE_VALUE);
        return [
            [
                $this->specification,
                $values,
                $types,
            ]
        ];
    }
}
