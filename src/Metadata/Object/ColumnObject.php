<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\Metadata\Object;

class ColumnObject
{
    /** @var string */
    protected $name = '';

    /** @var string */
    protected $tableName = '';

    /** @var string */
    protected $schemaName = '';

    /** @var int|null */
    protected $ordinalPosition;

    /** @var string */
    protected $columnDefault = '';

    /** @var bool */
    protected $isNullable = false;

    /** @var string */
    protected $dataType = '';

    /** @var int|null */
    protected $characterMaximumLength;

    /** @var int|null */
    protected $characterOctetLength;

    /** @var int|null */
    protected $numericPrecision;

    /** @var int|null */
    protected $numericScale;

    /** @var bool */
    protected $numericUnsigned = false;

    /** @var array */
    protected $errata = [];

    public function __construct(string $name, string $tableName, string $schemaName = '')
    {
        $this->setName($name);
        $this->setTableName($tableName);
        $this->setSchemaName($schemaName);
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getTableName() : string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName) : self
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function setSchemaName(string $schemaName) : void
    {
        $this->schemaName = $schemaName;
    }

    public function getSchemaName() : string
    {
        return $this->schemaName;
    }

    public function getOrdinalPosition() : ?int
    {
        return $this->ordinalPosition;
    }

    public function setOrdinalPosition(?int $ordinalPosition) : self
    {
        $this->ordinalPosition = $ordinalPosition;
        return $this;
    }

    public function getColumnDefault() : ?string
    {
        return $this->columnDefault;
    }

    public function setColumnDefault(string $columnDefault) : self
    {
        $this->columnDefault = $columnDefault;
        return $this;
    }

    public function getIsNullable() : bool
    {
        return $this->isNullable;
    }

    public function setIsNullable(bool $isNullable) : self
    {
        $this->isNullable = $isNullable;
        return $this;
    }

    public function isNullable() : bool
    {
        return $this->isNullable;
    }

    public function getDataType() : ?string
    {
        return $this->dataType;
    }

    public function setDataType(string $dataType) : self
    {
        $this->dataType = $dataType;
        return $this;
    }

    public function getCharacterMaximumLength() : ?int
    {
        return $this->characterMaximumLength;
    }

    public function setCharacterMaximumLength(?int $characterMaximumLength) : self
    {
        $this->characterMaximumLength = $characterMaximumLength;
        return $this;
    }

    public function getCharacterOctetLength() : ?int
    {
        return $this->characterOctetLength;
    }

    public function setCharacterOctetLength(?int $characterOctetLength) : self
    {
        $this->characterOctetLength = $characterOctetLength;
        return $this;
    }

    public function getNumericPrecision() : ?int
    {
        return $this->numericPrecision;
    }

    public function setNumericPrecision(?int $numericPrecision) : self
    {
        $this->numericPrecision = $numericPrecision;
        return $this;
    }

    public function getNumericScale() : ?int
    {
        return $this->numericScale;
    }

    public function setNumericScale(?int $numericScale) : self
    {
        $this->numericScale = $numericScale;
        return $this;
    }

    public function getNumericUnsigned() : bool
    {
        return $this->numericUnsigned;
    }

    public function setNumericUnsigned(bool $numericUnsigned) : self
    {
        $this->numericUnsigned = $numericUnsigned;
        return $this;
    }

    public function isNumericUnsigned() : bool
    {
        return $this->numericUnsigned;
    }

    public function getErratas() : array
    {
        return $this->errata;
    }

    public function setErratas(array $erratas) : self
    {
        foreach ($erratas as $name => $value) {
            $this->setErrata($name, $value);
        }
        return $this;
    }

    public function getErrata(string $errataName)
    {
        if (array_key_exists($errataName, $this->errata)) {
            return $this->errata[$errataName];
        }
    }

    public function setErrata(string $errataName, $errataValue) : self
    {
        $this->errata[$errataName] = $errataValue;
        return $this;
    }
}
