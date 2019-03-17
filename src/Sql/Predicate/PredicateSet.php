<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Predicate;

use Countable;
use Zend\Db\Sql\Exception;
use function array_merge;
use function count;
use function in_array;
use function is_string;
use function sprintf;
use function strpos;

class PredicateSet implements PredicateInterface, Countable
{
    public const COMBINED_BY_AND = 'AND';
    public const OP_AND          = 'AND';
    public const COMBINED_BY_OR  = 'OR';
    public const OP_OR           = 'OR';

    /** @var string  */
    protected $defaultCombination = self::COMBINED_BY_AND;

    /** @var PredicateInterface[] */
    protected $predicates         = [];

    public function __construct(?array $predicates = null, string $defaultCombination = self::COMBINED_BY_AND)
    {
        $this->defaultCombination = $defaultCombination;

        if ($predicates) {
            foreach ($predicates as $predicate) {
                $this->addPredicate($predicate);
            }
        }
    }

    public function addPredicate(PredicateInterface $predicate, ?string $combination = null) : self
    {
        if ($combination === null || ! in_array($combination, [self::OP_AND, self::OP_OR])) {
            $combination = $this->defaultCombination;
        }

        if ($combination === self::OP_OR) {
            $this->orPredicate($predicate);

            return $this;
        }

        $this->andPredicate($predicate);

        return $this;
    }

    /**
     * Add predicates to set
     *
     * @param PredicateInterface|\Closure|string|array $predicates
     * @param string                                   $combination
     * @return $this
     *
     * @throws Exception\InvalidArgumentException
     */
    public function addPredicates($predicates, string $combination = self::OP_AND) : self
    {
        if ($predicates === null) {
            throw new Exception\InvalidArgumentException('Predicate cannot be null');
        }

        if ($predicates instanceof PredicateInterface) {
            $this->addPredicate($predicates, $combination);

            return $this;
        }

        if ($predicates instanceof \Closure) {
            $predicates($this);

            return $this;
        }

        if (is_string($predicates)) {
            // String $predicate should be passed as an expression
            $predicates = (strpos($predicates, Expression::PLACEHOLDER) !== false)
                ? new Expression($predicates) : new Literal($predicates);
            $this->addPredicate($predicates, $combination);

            return $this;
        }

        if (is_array($predicates)) {
            foreach ($predicates as $pkey => $pvalue) {
                // loop through predicates
                if (is_string($pkey)) {
                    if (strpos($pkey, '?') !== false) {
                        // First, process strings that the abstraction replacement character ?
                        // as an Expression predicate
                        $predicates = new Expression($pkey, $pvalue);
                    } elseif ($pvalue === null) {
                        // Otherwise, if still a string, do something intelligent with the PHP type provided
                        // map PHP null to SQL IS NULL expression
                        $predicates = new IsNull($pkey);
                    } elseif (is_array($pvalue)) {
                        // if the value is an array, assume IN() is desired
                        $predicates = new In($pkey, $pvalue);
                    } elseif ($pvalue instanceof PredicateInterface) {
                        throw new Exception\InvalidArgumentException(
                            'Using Predicate must not use string keys'
                        );
                    } else {
                        // otherwise assume that array('foo' => 'bar') means "foo" = 'bar'
                        $predicates = new Operator($pkey, Operator::OP_EQ, $pvalue);
                    }
                } elseif ($pvalue instanceof PredicateInterface) {
                    // Predicate type is ok
                    $predicates = $pvalue;
                } else {
                    // must be an array of expressions (with int-indexed array)
                    $predicates = (strpos($pvalue, Expression::PLACEHOLDER) !== false)
                        ? new Expression($pvalue) : new Literal($pvalue);
                }

                $this->addPredicate($predicates, $combination);
            }
        }

        return $this;
    }

    public function getPredicates() : array
    {
        return $this->predicates;
    }

    public function orPredicate(PredicateInterface $predicate) : self
    {
        $this->predicates[] = [self::OP_OR, $predicate];

        return $this;
    }

    public function andPredicate(PredicateInterface $predicate) : self
    {
        $this->predicates[] = [self::OP_AND, $predicate];

        return $this;
    }

    public function getExpressionData() : array
    {
        $parts = [];

        for ($i = 0, $count = count($this->predicates); $i < $count; $i++) {
            /** @var $predicate PredicateInterface */
            $predicate = $this->predicates[$i][1];

            if ($predicate instanceof self) {
                $parts[] = '(';
            }

            $parts = array_merge($parts, $predicate->getExpressionData());

            if ($predicate instanceof self) {
                $parts[] = ')';
            }

            if (isset($this->predicates[$i + 1])) {
                $parts[] = sprintf(' %s ', $this->predicates[$i + 1][0]);
            }
        }

        return $parts;
    }

    public function count() : int
    {
        return count($this->predicates);
    }
}
