<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\ExpressionParameter;

class Like implements PredicateInterface
{
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
     * @return self
     */
    public function setIdentifier($identifier, $type = self::TYPE_IDENTIFIER)
    {
        $this->identifier = new ExpressionParameter($identifier, $type);
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param  string $like
     * @return self
     */
    public function setLike($like, $type = self::TYPE_VALUE)
    {
        $this->like = new ExpressionParameter($like, $type);
        return $this;
    }

    /**
     * @return string
     */
    public function getLike()
    {
        return $this->like;
    }
}
