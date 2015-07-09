<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Predicate;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Builder\Context;

class PredicateSetBuilder extends AbstractSqlBuilder
{
    /**
     * @param \Zend\Db\Sql\Predicate\PredicateSet $expression
     * @param Context $context
     * @return array
     */
    public function getExpressionData($expression, Context $context)
    {
        $this->validateSqlObject($expression, 'Zend\Db\Sql\Predicate\PredicateSet', __METHOD__);
        $predicates = $expression->getPredicates();
        $parts = [];
        for ($i = 0, $count = count($predicates); $i < $count; $i++) {
            /** @var $predicate PredicateInterface */
            $predicate = $predicates[$i][1];

            if ($predicate instanceof PredicateSet) {
                $parts[] = '(';
            }

            $parts = array_merge(
                $parts,
                $this->platformBuilder->getPlatformBuilder($predicate, $context)->getExpressionData($predicate, $context)
            );

            if ($predicate instanceof PredicateSet) {
                $parts[] = ')';
            }

            if (isset($predicates[$i+1])) {
                $parts[] = sprintf(' %s ', $predicates[$i+1][0]);
            }
        }
        return $parts;
    }
}
