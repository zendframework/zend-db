<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl\Index;

class Index extends AbstractIndex
{
    /**
     * @var array
     */
    protected $lengths;

    /**
     * @param  string|array|null $columns
     * @param  null|string $name
     * @param array $lengths
     */
    public function __construct($columns, $name = null, array $lengths = [])
    {
        $this->setColumns($columns);

        $this->name    = null === $name ? null : (string) $name;
        $this->lengths = $lengths;
    }

    public function setLengths($lengths)
    {
        $this->lengths = $lengths;
        return $this;
    }

    public function getLengths()
    {
        return $this->lengths;
    }
}
