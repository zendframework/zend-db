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

class IntegerBuilder extends ColumnBuilder
{
    public function build($column, Context $context)
    {
        $this->validateSqlObject($column, 'Zend\Db\Sql\Ddl\Column\Integer', __METHOD__);
        $data    = parent::build($column, $context);
        $options = $column->getOptions();

        if (isset($options['length'])) {
            $data[0][1][1] .= '(' . $options['length'] . ')';
        }

        return $data;
    }
}
