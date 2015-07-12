<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class CombineBuilder extends AbstractSqlBuilder
{
    /**
     * @param \Zend\Db\Sql\Combine $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Combine', __METHOD__);
        $res = [];
        foreach ($sqlObject->combine as $i => $combine) {
            $type = $i == 0
                    ? ''
                    : strtoupper($combine['type'] . ($combine['modifier'] ? ' ' . $combine['modifier'] : '')) . " ";
            $res[] = [
                'spec' => '%1$s%2$s',
                'params' => [
                    $type,
                    $combine['select']
                ],
            ];
        }
        return $res;
    }
}
