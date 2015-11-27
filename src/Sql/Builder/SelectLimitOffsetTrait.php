<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Predicate\Operator;

trait SelectLimitOffsetTrait
{
    /**
     * @param Select $sqlObject
     * @param Context $context
     * @return null|string|array
     */
    public function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Select', __METHOD__);
        if ($sqlObject->limit === null && $sqlObject->offset === null) {
            return parent::build($sqlObject, $context);
        }

        $sqlObject  = clone $sqlObject;
        $wrapObject = new Select();
        $newSelect  = new Select([
            'LIMIT_OFFSET_WRAP_2' => $wrapObject
                        ->columns([
                            Select::SQL_STAR,
                            'LIMIT_OFFSET_ROWNUM' => new Expression('ROW_NUMBER() OVER ()'),
                        ], false)
                        ->from([
                            'LIMIT_OFFSET_WRAP_1' => $sqlObject
                        ])
        ]);
        $newSelect->columns([Select::SQL_STAR], false);

        if ($sqlObject->offset !== null) {
            $offset = new ExpressionParameter((int) $sqlObject->offset, Expression::TYPE_VALUE, 'offset');
            $newSelect->where->greaterThan('LIMIT_OFFSET_ROWNUM', $offset);
        }

        if ($sqlObject->limit !== null) {
            $limit = new ExpressionParameter((int) $sqlObject->limit, Expression::TYPE_VALUE, 'limit');
            if ($sqlObject->offset !== null) {
                $offset->setName(['offset', 'offsetForSum']);
                $limit = new Operator($limit, '+', $offset);
            }
            $newSelect->where->lessThanOrEqualTo('LIMIT_OFFSET_ROWNUM', $limit);
        }
        unset($sqlObject->limit, $sqlObject->offset);
        return parent::build($newSelect, $context);
    }
}
