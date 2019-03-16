<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql;

use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Driver\DriverInterface;

/**
 * @property Where $where
 */
class Delete extends AbstractPreparableSql
{
    /**@#+
     * @const
     */
    public const SPECIFICATION_DELETE = 'delete';
    public const SPECIFICATION_WHERE = 'where';
    /**@#-*/

    /**
     * {@inheritDoc}
     */
    protected $specifications = [
        self::SPECIFICATION_DELETE => 'DELETE FROM %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
    ];

    /** @var string|TableIdentifier */
    protected $table = '';

    /** @var bool */
    protected $emptyWhereProtection = true;

    /** @var array */
    protected $set = [];

    /** @var null|string|Where */
    protected $where;

    /**
     * Constructor
     *
     * @param null|string|TableIdentifier $table
     */
    public function __construct(?$table = null)
    {
        if ($table) {
            $this->from($table);
        }
        $this->where = new Where();
    }

    /**
     * Create from statement
     *
     * @param string|TableIdentifier $table
     *
     * @return self Provides a fluent interface
     */
    public function from($table) : self
    {
        $this->table = $table;

        return $this;
    }

    public function getRawState(?string $key = null)
    {
        $rawState = [
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set,
            'where' => $this->where
        ];

        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Create where clause
     *
     * @param Where|\Closure|string|array $predicate
     * @param string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @return self Provides a fluent interface
     */
    public function where($predicate, string $combination = Predicate\PredicateSet::OP_AND) : self
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    protected function processDelete(
        PlatformInterface $platform,
        ?DriverInterface $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) : string {
        return sprintf(
            $this->specifications[static::SPECIFICATION_DELETE],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer)
        );
    }

    protected function processWhere(
        PlatformInterface   $platform,
        ?DriverInterface    $driver = null,
        ?ParameterContainer $parameterContainer = null
    ) : ?string {
        if ($this->where->count() === 0) {
            return null;
        }

        return sprintf(
            $this->specifications[static::SPECIFICATION_WHERE],
            $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where')
        );
    }

    public function __get(string $name) : ?Where
    {
        switch (strtolower($name)) {
            case 'where':
                return $this->where;
        }
    }
}
