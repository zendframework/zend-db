<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGatewayInterface;

class FeatureSet
{
    public const APPLY_HALT = 'halt';

    /** @var AbstractTableGateway */
    protected $tableGateway;

    /** @var AbstractFeature[] */
    protected $features = [];

    /** @var array */
    protected $magicSpecifications = [];

    public function __construct(array $features = [])
    {
        if ($features) {
            $this->addFeatures($features);
        }
    }

    public function setTableGateway(AbstractTableGateway $tableGateway) : self
    {
        $this->tableGateway = $tableGateway;
        foreach ($this->features as $feature) {
            $feature->setTableGateway($this->tableGateway);
        }
        return $this;
    }

    public function getFeatureByClassName($featureClassName)
    {
        $feature = false;
        foreach ($this->features as $potentialFeature) {
            if ($potentialFeature instanceof $featureClassName) {
                $feature = $potentialFeature;
                break;
            }
        }
        return $feature;
    }

    /**
     * @param AbstractFeature[] $features
     * @return self Provides a fluent interface
     */
    public function addFeatures(array $features) : self
    {
        foreach ($features as $feature) {
            $this->addFeature($feature);
        }
        return $this;
    }

    public function addFeature(AbstractFeature $feature) : self
    {
        if ($this->tableGateway instanceof TableGatewayInterface) {
            $feature->setTableGateway($this->tableGateway);
        }
        $this->features[] = $feature;
        return $this;
    }

    public function apply($method, $args) : void
    {
        foreach ($this->features as $feature) {
            if (method_exists($feature, $method)) {
                $return = call_user_func_array([$feature, $method], $args);
                if ($return === self::APPLY_HALT) {
                    break;
                }
            }
        }
    }

    public function canCallMagicGet(string $property) : bool
    {
        return false;
    }

    public function callMagicGet(string $property) : void
    {
    }

    public function canCallMagicSet(string $property) : bool
    {
        return false;
    }

    public function callMagicSet(string $property, $value) : void
    {
    }

    /**
     * Is the method requested available in one of the added features
     * @param string $method
     * @return bool
     */
    public function canCallMagicCall(string $method) : bool
    {
        if (! empty($this->features)) {
            foreach ($this->features as $feature) {
                if (method_exists($feature, $method)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function callMagicCall(string $method, array $arguments)
    {
        foreach ($this->features as $feature) {
            if (method_exists($feature, $method)) {
                return $feature->$method($arguments);
            }
        }
    }
}
