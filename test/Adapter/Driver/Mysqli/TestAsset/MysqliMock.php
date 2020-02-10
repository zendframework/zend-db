<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Adapter\Driver\Mysqli\TestAsset;

class MysqliMock extends \mysqli
{
    protected $mockStatement;

    public function __construct()
    {
        // not sure what to do here
        // all I really need is mysqli->prepare so line 206 in Statement.php throws the correct exception
        // $this->resource = $this->mysqli->prepare($sql);
        parent::__construct();
    }
}