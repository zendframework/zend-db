<?php

/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl\Column;

/**
 * Column for PostgreSQL to automatically increment column.
 *
 * Similar to MySQL's autoincrement, but without performance issues.
 * Used in conjunction with SequenceFeature.
 */
class Serial extends Column
{
    protected $type = 'SERIAL';
}
