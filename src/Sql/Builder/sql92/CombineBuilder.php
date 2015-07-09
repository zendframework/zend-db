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
    const SPECIFICATION_COMBINE = 'combine';

    protected $specifications = [
        self::SPECIFICATION_COMBINE => '%1$s (%2$s) ',
    ];

    /**
     * @param \Zend\Db\Sql\Combine $sqlObject
     * @param Context $context
     * @return string
     */
    protected function buildSqlString($sqlObject, Context $context)
    {
        $COMBINE = $sqlObject->combine;
        if (!$COMBINE) {
            return;
        }

        $sql = '';
        foreach ($COMBINE as $i => $combine) {
            $type = $i == 0
                    ? ''
                    : strtoupper($combine['type'] . ($combine['modifier'] ? ' ' . $combine['modifier'] : ''));
            $select = $this->buildSubSelect($combine['select'], $context);
            $sql .= sprintf(
                $this->specifications[self::SPECIFICATION_COMBINE],
                $type,
                $select
            );
        }
        return trim($sql, ' ');
    }
}
