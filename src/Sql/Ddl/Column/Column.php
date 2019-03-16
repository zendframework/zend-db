<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Constraint\ConstraintInterface;

class Column implements ColumnInterface
{
    /** @var null|string|int */
    protected $default;

    /** @var bool */
    protected $isNullable = false;

    /** @var string */
    protected $name = '';

    /** @var array */
    protected $options = [];

    /** @var ConstraintInterface[] */
    protected $constraints = [];

    /** @var string */
    protected $specification = '%s %s';

    /** @var string */
    protected $type = 'INTEGER';

    /**
     * @param string $name
     * @param bool        $nullable
     * @param mixed|null  $default
     * @param mixed[]     $options
     */
    public function __construct(string $name = '', bool $nullable = false, ?$default = null, array $options = [])
    {
        $this->setName($name);
        $this->setNullable($nullable);
        $this->setDefault($default);
        $this->setOptions($options);
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param bool $nullable
     * @return self Provides a fluent interface
     */
    public function setNullable(bool $nullable) : self
    {
        $this->isNullable = (bool) $nullable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNullable() : bool
    {
        return $this->isNullable;
    }

    /**
     * @param null|string|int $default
     *
     * @return self Provides a fluent interface
     */
    public function setDefault(?$default) : self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return null|string|int
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param array $options
     *
     * @return self Provides a fluent interface
     */
    public function setOptions(array $options) : self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return self Provides a fluent interface
     */
    public function setOption(string $name, string $value) : self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * @param ConstraintInterface $constraint
     *
     * @return self Provides a fluent interface
     */
    public function addConstraint(ConstraintInterface $constraint) : self
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @return array
     */
    public function getExpressionData() : array
    {
        $spec = $this->specification;

        $params   = [];
        $params[] = $this->name;
        $params[] = $this->type;

        $types = [self::TYPE_IDENTIFIER, self::TYPE_LITERAL];

        if (! $this->isNullable) {
            $spec .= ' NOT NULL';
        }

        if ($this->default !== null) {
            $spec    .= ' DEFAULT %s';
            $params[] = $this->default;
            $types[]  = self::TYPE_VALUE;
        }

        $data = [[
            $spec,
            $params,
            $types,
        ]];

        foreach ($this->constraints as $constraint) {
            $data[] = ' ';
            $data = array_merge($data, $constraint->getExpressionData());
        }

        return $data;
    }
}
