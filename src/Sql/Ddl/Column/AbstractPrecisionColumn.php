<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl\Column;

abstract class AbstractPrecisionColumn extends AbstractLengthColumn
{
    /** @var int|null */
    protected $decimal;

    /**
     * {@inheritDoc}
     *
     * @param int      $digits
     * @param int|null $decimal
     */
    public function __construct(
        string $name = '',
        ?int $digits = null,
        ?int $decimal = null,
        bool $nullable = false,
        ?$default = null,
        array $options = []
    ) {
        $this->setDecimal($decimal);

        parent::__construct($name, $digits, $nullable, $default, $options);
    }

    /**
     * @param int $digits
     *
     * @return self
     */
    public function setDigits(int $digits) : self
    {
        return $this->setLength($digits);
    }

    /**
     * @return int
     */
    public function getDigits() : int
    {
        return $this->getLength();
    }

    /**
     * @param int|null $decimal
     *
     * @return self Provides a fluent interface
     */
    public function setDecimal(?int $decimal) : self
    {
        $this->decimal = null === $decimal ? null : (int) $decimal;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDecimal() : ?int
    {
        return $this->decimal;
    }

    /**
     * {@inheritDoc}
     */
    protected function getLengthExpression() : string
    {
        if ($this->decimal !== null) {
            return $this->length . ',' . $this->decimal;
        }

        return (string) $this->length;
    }
}
