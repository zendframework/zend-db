<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata\Object;

class ViewObject extends AbstractTableObject
{
    /** @var string */
    protected $viewDefinition = '';

    /** @var string */
    protected $checkOption = '';

    /** @var bool */
    protected $isUpdatable = false;

    public function getViewDefinition() : string
    {
        return $this->viewDefinition;
    }

    public function setViewDefinition(string $viewDefinition) : self
    {
        $this->viewDefinition = $viewDefinition;
        return $this;
    }

    public function getCheckOption() : string
    {
        return $this->checkOption;
    }

    public function setCheckOption(string $checkOption) : self
    {
        $this->checkOption = $checkOption;
        return $this;
    }

    public function getIsUpdatable() : bool
    {
        return $this->isUpdatable;
    }

    public function setIsUpdatable(bool $isUpdatable) : self
    {
        $this->isUpdatable = $isUpdatable;
        return $this;
    }

    public function isUpdatable() : bool
    {
        return $this->isUpdatable;
    }
}
