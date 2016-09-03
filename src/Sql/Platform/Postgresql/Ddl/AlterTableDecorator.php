<?php

namespace Zend\Db\Sql\Platform\Postgresql\Ddl;

use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class AlterTableDecorator extends AlterTable implements PlatformDecoratorInterface
{
    /**
     * @var AlterTable
     */
    private $subject;

    /**
     * @inheritDoc
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

}
