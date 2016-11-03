<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

interface ExpressionInterface
{
    const PLACEHOLDER = '?';

    const TYPE_IDENTIFIER = 'identifier';
    const TYPE_VALUE = 'value';
    const TYPE_LITERAL = 'literal';
    const TYPE_SELECT = 'select';
}
