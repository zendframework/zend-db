<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl\Column;

abstract class AbstractLengthColumn extends Column implements AbstractLengthColumnInterface
{
    /**
     * @var int
     */
    protected $length;

    protected $isMultibyte = false;

    /**
     * {@inheritDoc}
     *
     * @param int $length
     */
    public function __construct($name, $length = null, $nullable = false, $default = null, array $options = [])
    {
        $this->setLength($length);

        parent::__construct($name, $nullable, $default, $options);
    }

    /**
     * @param  int $length
     * @return self Provides a fluent interface
     */
    public function setLength(?int $length) : self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength() : ?int
    {
        return $this->length;
    }

    /**
     * @return string
     */
    protected function getLengthExpression() : string
    {
        return (string) $this->length;
    }

    /**
     * @return AbstractLengthColumn
     */
    public function enableMultibyte(): self
    {
        $this->isMultibyte = true;

        return $this;
    }

    /**
     * @return AbstractLengthColumn
     */
    public function disableMultibyte(): self
    {
        $this->isMultibyte = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultibyte(): bool
    {
        return $this->isMultibyte;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $data = parent::getExpressionData();

        if ($this->getLengthExpression()) {
            $data[0][1][1] .= '(' . $this->getLengthExpression() . ')';
        }

        return $data;
    }
}
