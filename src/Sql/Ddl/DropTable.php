<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Sql\AbstractSqlObject;

/**
 * @property null|string|array|TableIdentifier $table
 */
class DropTable extends AbstractSqlObject
{
    /**
     * @var string
     */
    protected $table = '';

    protected $__getProperties = [
        'table',
    ];

    /**
     * @param string $table
     */
    public function __construct($table = '')
    {
        parent::__construct();
        $this->table = $table;
    }
}
