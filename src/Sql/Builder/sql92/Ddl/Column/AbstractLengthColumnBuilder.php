<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl\Column;

use Zend\Db\Sql\Builder\Context;

class AbstractLengthColumnBuilder extends ColumnBuilder
{
    public function build($column, Context $context)
    {
        $this->validateSqlObject($column, 'Zend\Db\Sql\Ddl\Column\AbstractLengthColumn', __METHOD__);
        $data = parent::build($column, $context);

        if ($this->getLengthExpression($column)) {
            $data[0]['params'][1]->setValue($data[0]['params'][1]->getValue() . '(' . $this->getLengthExpression($column) . ')');
        }

        return $data;
    }

    protected function getLengthExpression($column)
    {
        return (string) $column->getLength();
    }
}
