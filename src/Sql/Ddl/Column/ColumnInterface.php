<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl\Column;

use Zend\Db\Sql\ExpressionInterface;

/**
 * Interface ColumnInterface describes the protocol on how Column objects interact
 *
 * @package Zend\Db\Sql\Ddl\Column
 */
interface ColumnInterface extends ExpressionInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return bool
     */
    public function isNullable() : bool;

    /**
     * @return null|string|int
     */
    public function getDefault();

    /**
     * @return array
     */
    public function getOptions() : array;
}
