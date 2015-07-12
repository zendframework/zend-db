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
use Zend\Db\Sql\Builder\Context;

class InBuilder extends AbstractSqlBuilder
{
    protected $specification = '%s IN %s';

    /**
     * @param \Zend\Db\Sql\Predicate\In $expression
     * @param Context $context
     * @return array
     */
    protected function build($expression, Context $context)
    {
        $this->validateSqlObject($expression, 'Zend\Db\Sql\Predicate\In', __METHOD__);
        $identifier = $expression->getIdentifier();
        $values = $expression->getValueSet();
        $replacements = [];

        if (is_array($identifier)) {
            $identifierSpecFragment = '(' . implode(', ', array_fill(0, count($identifier), '%s')) . ')';
            $replacements = $identifier;
        } else {
            $identifierSpecFragment = '%s';
            $replacements[] = $identifier;
        }

        if (is_array($values)) {
            $replacements = array_merge($replacements, $values);
            $specification = vsprintf(
                $this->specification,
                [$identifierSpecFragment, '(' . implode(', ', array_fill(0, count($values), '%s')) . ')']
            );
        } else {
            $specification = vsprintf(
                $this->specification,
                [$identifierSpecFragment, '%s']
            );
            $replacements[] = $values;
        }

        return [[
            'spec' => $specification,
            'params' => $replacements,
        ]];
    }
}
