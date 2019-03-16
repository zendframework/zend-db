<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Sql\Ddl\Constraint;

class ForeignKey extends AbstractConstraint
{
    /** @var string */
    protected $onDeleteRule = 'NO ACTION';

    /** @var string */
    protected $onUpdateRule = 'NO ACTION';

    /** @var string[] */
    protected $referenceColumn = [];

    /** @var string */
    protected $referenceTable = '';

    /**
     * {@inheritDoc}
     */
    protected $columnSpecification = 'FOREIGN KEY (%s) ';

    /** @var string[] */
    protected $referenceSpecification = [
        'REFERENCES %s ',
        'ON DELETE %s ON UPDATE %s'
    ];

    /**
     * @param null|string       $name
     * @param null|string|array $columns
     * @param string            $referenceTable
     * @param null|string|array $referenceColumn
     * @param null|string       $onDeleteRule
     * @param null|string       $onUpdateRule
     */
    public function __construct(
        ?string $name,
        ?$columns,
        string $referenceTable,
        ?$referenceColumn,
        ?string $onDeleteRule = null,
        ?string $onUpdateRule = null
    ) {
        $this->setName($name);
        $this->setColumns($columns);
        $this->setReferenceTable($referenceTable);
        $this->setReferenceColumn($referenceColumn);

        if ($onDeleteRule) {
            $this->setOnDeleteRule($onDeleteRule);
        }

        if ($onUpdateRule) {
            $this->setOnUpdateRule($onUpdateRule);
        }
    }

    /**
     * @param  string $referenceTable
     *
     * @return self Provides a fluent interface
     */
    public function setReferenceTable(string $referenceTable) : self
    {
        $this->referenceTable = (string) $referenceTable;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceTable() : string
    {
        return $this->referenceTable;
    }

    /**
     * @param  null|string|array $referenceColumn
     * @return self Provides a fluent interface
     */
    public function setReferenceColumn(?$referenceColumn) : self
    {
        $this->referenceColumn = (array) $referenceColumn;

        return $this;
    }

    /**
     * @return array
     */
    public function getReferenceColumn() : array
    {
        return $this->referenceColumn;
    }

    /**
     * @param  string $onDeleteRule
     * @return self Provides a fluent interface
     */
    public function setOnDeleteRule(string $onDeleteRule) : self
    {
        $this->onDeleteRule = (string) $onDeleteRule;

        return $this;
    }

    /**
     * @return string
     */
    public function getOnDeleteRule() : string
    {
        return $this->onDeleteRule;
    }

    /**
     * @param  string $onUpdateRule
     * @return self Provides a fluent interface
     */
    public function setOnUpdateRule(string $onUpdateRule) : self
    {
        $this->onUpdateRule = (string) $onUpdateRule;

        return $this;
    }

    /**
     * @return string
     */
    public function getOnUpdateRule() : string
    {
        return $this->onUpdateRule;
    }

    /**
     * @return array
     */
    public function getExpressionData() : array
    {
        $data         = parent::getExpressionData();
        $colCount     = count($this->referenceColumn);
        $newSpecTypes = [self::TYPE_IDENTIFIER];
        $values       = [$this->referenceTable];

        $data[0][0] .= $this->referenceSpecification[0];

        if ($colCount) {
            $values       = array_merge($values, $this->referenceColumn);
            $newSpecParts = array_fill(0, $colCount, '%s');
            $newSpecTypes = array_merge($newSpecTypes, array_fill(0, $colCount, self::TYPE_IDENTIFIER));

            $data[0][0] .= sprintf('(%s) ', implode(', ', $newSpecParts));
        }

        $data[0][0] .= $this->referenceSpecification[1];

        $values[]       = $this->onDeleteRule;
        $values[]       = $this->onUpdateRule;
        $newSpecTypes[] = self::TYPE_LITERAL;
        $newSpecTypes[] = self::TYPE_LITERAL;

        $data[0][1] = array_merge($data[0][1], $values);
        $data[0][2] = array_merge($data[0][2], $newSpecTypes);

        return $data;
    }
}
