<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

class TableSource
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @var TableIdentifier|SelectableInterface|ExpressionInterface
     */
    protected $source;

    public static function factory($source, $alias = null)
    {
        if ($source instanceof self) {
            return $source;
        }

        if (is_array($source) && is_string(key($source))) {
            $alias = key($source);
            $source  = current($source);
        }

        return new self(
            $source,
            $alias
        );
    }

    /**
     * @param string|array|TableIdentifier|SelectableInterface|ExpressionInterface $source
     * @param string $alias
     */
    public function __construct($source, $alias = null)
    {
        $this->setSource($source);
        $this->alias = $alias;
    }

    /**
     * @param string $alias
     * @return self
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string|array|TableIdentifier|SelectableInterface|ExpressionInterface $source
     * @return self
     */
    public function setSource($source)
    {
        if (is_string($source) || is_array($source)) {
            $source = new TableIdentifier($source);
        } elseif ($source !== null && !$source instanceof TableIdentifier && !$source instanceof ExpressionInterface && !$source instanceof SelectableInterface) {
            throw new Exception\InvalidArgumentException('invalid $source parameter');
        }

        $this->source = $source;
        return $this;
    }

    /**
     * @return TableIdentifier|SelectableInterface|ExpressionInterface
     */
    public function getSource()
    {
        return $this->source;
    }
}
