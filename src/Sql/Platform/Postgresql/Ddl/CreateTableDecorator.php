<?php
/**
 * @see       http://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Db\Sql\Platform\Postgresql\Ddl;

use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

// TODO: need to be merged with Postgres PRs depending on which one is accepted first
// this is a stub to void breaking national chars for Postgres, as it is the odd one out
class CreateTableDecorator extends CreateTable implements PlatformDecoratorInterface
{

    protected $subject;

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    protected function substituteMultibyteType(
        Column\ColumnInterface $column,
        string $sql
    ): string {
        return $sql;
    }
}
