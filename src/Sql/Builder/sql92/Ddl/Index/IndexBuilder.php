<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl\Index;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;

class IndexBuilder extends AbstractSqlBuilder
{
    /**
     * @var string
     */
    protected $specification = 'INDEX %s(...)';

    /**
     * @param \Zend\Db\Sql\Ddl\Index\AbstractIndex $index
     * @param Context $context
     * @return array
     */
    protected function build($index, Context $context)
    {
        $this->validateSqlObject($index, 'Zend\Db\Sql\Ddl\Index\AbstractIndex', __METHOD__);
        $properties = [
            new ExpressionParameter($index->getName() ?: '', ExpressionInterface::TYPE_IDENTIFIER)
        ];

        $spec = '';
        foreach ($index->getColumns() as $i => $column) {
            $properties[] = new ExpressionParameter($column, ExpressionInterface::TYPE_IDENTIFIER);
            $spec .= '%s' . (isset($index->getLengths()[$i]) ? '(' . $index->getLengths()[$i] . ')' : '') . ', ';
        }

        return [[
            'spec' => str_replace('...', rtrim($spec, ', '), $this->specification),
            'params' => $properties,
        ]];
    }
}
