<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

use Zend\Db\Sql\Exception\InvalidArgumentException;
use function current;
use function get_class;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_int;
use function is_object;
use function is_scalar;
use function key;

abstract class AbstractExpression implements ExpressionInterface
{
    /** @var string[] */
    protected $allowedTypes = [
        self::TYPE_IDENTIFIER,
        self::TYPE_LITERAL,
        self::TYPE_SELECT,
        self::TYPE_VALUE,
    ];

    /**
     * Normalize Argument
     *
     * @param mixed $argument
     * @param string $defaultType
     * @return array
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeArgument($argument, string $defaultType = self::TYPE_VALUE) : array
    {
        if ($argument instanceof ExpressionInterface || $argument instanceof SqlInterface) {
            return $this->buildNormalizedArgument($argument, self::TYPE_VALUE);
        }

        if (is_scalar($argument) || $argument === null) {
            return $this->buildNormalizedArgument($argument, $defaultType);
        }

        if (is_array($argument)) {
            $value = current($argument);

            if ($value instanceof ExpressionInterface || $value instanceof SqlInterface) {
                return $this->buildNormalizedArgument($value, self::TYPE_VALUE);
            }

            $key = key($argument);

            if (is_int($key) && ! in_array($value, $this->allowedTypes)) {
                return $this->buildNormalizedArgument($value, $defaultType);
            }

            return $this->buildNormalizedArgument($key, $value);
        }

        throw new InvalidArgumentException(sprintf(
            '$argument should be %s or %s or %s or %s or %s, "%s" given',
            'null',
            'scalar',
            'array',
            ExpressionInterface::class,
            SqlInterface::class,
            is_object($argument) ? get_class($argument) : gettype($argument)
        ));
    }

    /**
     * @param mixed  $argument
     * @param string $argumentType
     * @return mixed[]
     *
     * @throws Exception\InvalidArgumentException
     */
    private function buildNormalizedArgument($argument, string $argumentType) : array
    {
        if (! in_array($argumentType, $this->allowedTypes)) {
            throw new InvalidArgumentException(sprintf(
                'Argument type should be in array(%s)',
                implode(',', $this->allowedTypes)
            ));
        }

        return [
            $argument,
            $argumentType,
        ];
    }
}
