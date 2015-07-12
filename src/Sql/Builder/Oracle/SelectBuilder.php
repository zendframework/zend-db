<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\Oracle;

use Zend\Db\Sql\Builder\sql92\SelectBuilder as BaseBuilder;
use Zend\Db\Sql\Builder\Builder;
use Zend\Db\Sql\Builder\SelectLimitOffsetTrait;

class SelectBuilder extends BaseBuilder
{
    use SelectLimitOffsetTrait;

    /**
     * {@inheritDoc}
     */
    public function __construct(Builder $platformBuilder)
    {
        parent::__construct($platformBuilder);
        $asSpec = [
            'byCount' => [
                1 => '%1$s', 2 => '%1$s %2$s'
            ],
        ];
        $this->selectColumnsTableSpecification['byArgNumber'][2] = $asSpec;
        $this->selectFullSpecification['byArgNumber'][3] = $asSpec;
        $this->joinsSpecification['forEach']['byArgNumber'][2] = $asSpec;
    }
}
