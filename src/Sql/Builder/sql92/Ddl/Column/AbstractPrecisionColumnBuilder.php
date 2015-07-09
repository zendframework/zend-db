<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl\Column;

class AbstractPrecisionColumnBuilder extends AbstractLengthColumnBuilder
{
    protected function getLengthExpression($column)
    {
        if ($column->getDecimal() !== null) {
            return $column->getLength() . ',' . $column->getDecimal();
        }

        return $column->getLength();
    }
}
