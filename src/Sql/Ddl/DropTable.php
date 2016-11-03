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
use Zend\Db\Sql\TableIdentifier;

/**
 * @property TableIdentifier $table
 * @property bool $ifExists
 */
class DropTable extends AbstractSqlObject
{
    /**
     * @var TableIdentifier
     */
    protected $table;

    protected $ifExists = false;

    protected $__getProperties = [
        'table',
        'ifExists',
    ];

    /**
     * @param string $table
     */
    public function __construct($table = null)
    {
        parent::__construct();
        $this->table = TableIdentifier::factory($table);
    }

    public function ifExists($ifExists = false)
    {
        $this->ifExists = (bool)$ifExists;
        return $this;
    }
}
