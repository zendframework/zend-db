<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl\Constraint;

use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;

class ForeignKeyBuilder extends AbstractBuilder
{
    /**
     * {@inheritDoc}
     */
    protected $columnSpecification = 'FOREIGN KEY (%s) ';

    /**
     * @var string[]
     */
    protected $referenceSpecification = [
        'REFERENCES %s ',
        'ON DELETE %s ON UPDATE %s'
    ];

    /**
     * @param \Zend\Db\Sql\Ddl\Constraint\ForeignKey $constraint
     * @param Context $context
     * @return array
     */
    protected function build($constraint, Context $context)
    {
        $this->validateSqlObject($constraint, 'Zend\Db\Sql\Ddl\Constraint\ForeignKey', __METHOD__);
        $data         = parent::build($constraint, $context);
        $parameters   = &$data[0]['params'];
        $parameters[] = new ExpressionParameter($constraint->getReferenceTable(), ExpressionInterface::TYPE_IDENTIFIER);

        $spec = '';
        foreach ($constraint->getReferenceColumn() as $refColumn) {
            $parameters[] = new ExpressionParameter($refColumn, ExpressionInterface::TYPE_IDENTIFIER);
            $spec .= '%s, ';
        }
        if ($spec) {
            $spec = '(' . rtrim($spec, ', ') . ') ';
        }

        $data[0]['spec'] .= $this->referenceSpecification[0] . $spec . $this->referenceSpecification[1];
        $parameters[] = new ExpressionParameter($constraint->getOnDeleteRule(), ExpressionInterface::TYPE_LITERAL);
        $parameters[] = new ExpressionParameter($constraint->getOnUpdateRule(), ExpressionInterface::TYPE_LITERAL);

        return $data;
    }
}
