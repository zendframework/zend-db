<?php

/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\SqlServer\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class AlterTableDecorator extends AlterTable implements PlatformDecoratorInterface
{
    /**
     * @var AlterTable
     */
    protected $subject;

    protected $alterSpecifications = [
        self::ADD_COLUMNS => [
            "%1\$s" => [
                [1 => "ADD %1\$s,\n", 'combinedby' => ''],
            ],
        ],
    ];

    /**
     * @param AlterTable $subject
     *
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->specifications = array_merge($this->specifications, $this->alterSpecifications);
        $this->subject->specifications = $this->specifications;

        return $this;
    }

    protected function processAddColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = [];
        foreach ($this->addColumns as $column) {
            $sqls[] = $this->processExpression($column, $adapterPlatform);
        }

        return [$sqls];
    }
}
