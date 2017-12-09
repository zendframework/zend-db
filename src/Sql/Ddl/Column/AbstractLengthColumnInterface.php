<?php
/**
 * @see       http://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Db\Sql\Ddl\Column;

/**
 * Interface AbstractLengthColumnInterface describes the protocol of how variable length column objects interact
 *
 * @package Zend\Db\Sql\Ddl\Column
 */
interface AbstractLengthColumnInterface extends ColumnInterface
{

    /**
     * @param int $length
     *
     * @return AbstractLengthColumn
     */
    public function setLength(int $length) : AbstractLengthColumn;

    /**
     * @return int
     */
    public function getLength() : ?int;

    /**
     * Enable multibyte support for columns storing non-latin values
     *
     * @return AbstractLengthColumn
     */
    public function enableMultibyte() : AbstractLengthColumn;

    /**
     * Disables multibyte support for columns storing only latin-based values
     *
     * @return AbstractLengthColumn
     */
    public function disableMultibyte() : AbstractLengthColumn;

    /**
     * @return bool Returns whether column supports storage of multibyte non-latin values
     */
    public function isMultibyte() : bool;
}
