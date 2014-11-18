<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Oracle;

use Zend\Db\Sql\Platform\AbstractPlatform;
use Zend\Db\Adapter\Platform\Oracle as DefaultPlatform;

class Oracle extends AbstractPlatform
{
    public function __construct(SelectDecorator $selectDecorator = null)
    {
        $this->defaultPlatform = new DefaultPlatform;
        $this->setTypeDecorator('Zend\Db\Sql\Select', ($selectDecorator) ?: new SelectDecorator());
    }
}
