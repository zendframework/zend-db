<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata\Object;

abstract class AbstractTableObject
{
    /** @var string */
    protected $name = '';

    /**  @var string */
    protected $type = '';

    /** @var array */
    protected $columns = [];

    /**  @var array */
    protected $constraints = [];

    public function __construct(string $name = '')
    {
        $this->setName($name);
    }

    public function setColumns(array $columns) : void
    {
        $this->columns = $columns;
    }

    public function getColumns() : array
    {
        return $this->columns;
    }

    public function setConstraints(array $constraints) : void
    {
        $this->constraints = $constraints;
    }

    public function getConstraints() : array
    {
        return $this->constraints;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
