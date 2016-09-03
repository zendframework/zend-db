<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Postgresql\Ddl;

use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;

class CreateTableDecorator extends CreateTable implements PlatformDecoratorInterface
{

    /**
     * @var CreateTable
     */
    protected $subject;

    /**
     * @inheritDoc
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    protected function processStatementEnd(PlatformInterface $adapterPlatform = null)
    {
        return ["\n);"];
    }

}