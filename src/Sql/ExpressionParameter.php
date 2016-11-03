<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

class ExpressionParameter
{
    protected $value;

    protected $type;

    protected $name;

    protected $options = [];

    /**
     * @param mixed $value
     * @param string $type
     * @param string $name
     */
    public function __construct($value, $type = ExpressionInterface::TYPE_VALUE, $name = null)
    {
        if ($value instanceof self) {
            $this->value = $value->value;
            $this->type = $value->type;
            $this->name = $value->name;
            $this->options = $value->options;
            return;
        }

        if (is_array($type)) {
            $this->options = $type;
            $type = ExpressionInterface::TYPE_VALUE;
        }
        if (is_array($value)) {
            if (is_string(key($value))) {
                $type  = current($value);
                $value = key($value);
            } elseif (count($value) == 1) {
                $type  = current($value);
                $value = key($value);
            } else {
                $type  = isset($value[1]) ? $value[1] : $type;
                $value = $value[0];
            }
        }

        if ($value instanceof ExpressionInterface || $value instanceof SqlObjectInterface) {
            $type = ExpressionInterface::TYPE_SELECT;
        }

        $this->value = $value;
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * @param mixed $value
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name=>$value) {
            $this->options[$name] = $value;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return null|mixed
     */
    public function getOption($name)
    {
        return array_key_exists($name, $this->options)
                ? $this->options[$name]
                : null;
    }
}
