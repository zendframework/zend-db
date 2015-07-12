<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\SqlServer;

use Zend\Db\Sql\Builder\sql92\SelectBuilder as BaseBuilder;
use Zend\Db\Sql\Builder\SelectLimitOffsetTrait;

class SelectBuilder extends BaseBuilder
{
    use SelectLimitOffsetTrait;
}
