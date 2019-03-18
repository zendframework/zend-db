<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl\Column;

/**
 * Column representing a FLOAT type.
 *
 * Cannot name a class "float" starting in PHP 7, as it's a reserved keyword;
 * hence, "floating", with a type of "FLOAT".
 */
class Floating extends AbstractPrecisionColumn
{
    /** @var string */
    protected $type = 'FLOAT';
}
