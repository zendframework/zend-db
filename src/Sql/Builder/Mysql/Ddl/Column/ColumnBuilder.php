<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\Mysql\Ddl\Column;

use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Builder\Builder;
use Zend\Db\Sql\Builder\sql92\Ddl\Column\ColumnBuilder as BaseBuilder;

class ColumnBuilder extends BaseBuilder
{
    /**
     * {@inheritDoc}
     */
    public function __construct(Builder $platformBuilder)
    {
        parent::__construct($platformBuilder);
        $this->specifications = [
            'name'        => $this->specifications['name'],
            'type'        => $this->specifications['type'],
            'typeData'    => [
                'spec' => [
                    'implode' => ' ',
                ],
                'subProperties'    => [
                    'unsigned' => [
                        'valueMap' => [true => 'UNSIGNED', false => null],
                    ],
                    'zerofill' => [
                        'valueMap' => [true => 'ZEROFILL', false => null],
                    ],
                ],
            ],
            'nullable'     => $this->specifications['nullable'],
            'identity'     => [
                'valueMap' => [true => 'AUTO_INCREMENT', false => null],
            ],
            'constraints'  => $this->specifications['constraints'],
            'default'      => $this->specifications['default'],
            'comment'      => [
                'spec'             => 'COMMENT %s',
                'propertyType'     => ExpressionInterface::TYPE_VALUE,
            ],
            'columnformat' => 'COLUMN_FORMAT %s',
            'storage'      => 'STORAGE %s',
            'on_update'    => 'ON UPDATE %s',
        ];
    }
}
