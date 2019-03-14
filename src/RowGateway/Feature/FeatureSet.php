<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\RowGateway\Feature;

use Zend\Db\RowGateway\AbstractRowGateway;

class FeatureSet
{
    public const APPLY_HALT = 'halt';

    /** @var AbstractRowGateway */
    protected $rowGateway;

    /** @var AbstractFeature[] */
    protected $features = [];

    /** @var array */
    protected $magicSpecifications = [];

    public function __construct(array $features = [])
    {
        $this->addFeatures($features);
    }

    public function setRowGateway(AbstractRowGateway $rowGateway) : self
    {
        $this->rowGateway = $rowGateway;
        foreach ($this->features as $feature) {
            $feature->setRowGateway($this->rowGateway);
        }
        return $this;
    }

    /**
     * @param mixed $featureClassName
     * @return bool|AbstractFeature
     */
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

    public function addFeatures(array $features) : self
    {
        foreach ($features as $feature) {
            $this->addFeature($feature);
        }
        return $this;
    }

    public function addFeature(AbstractFeature $feature) : self
    {
        $this->features[] = $feature;
        $feature->setRowGateway($feature);
        return $this;
    }

    public function apply(string $method, array $args) : void
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
        return null;
    }

    public function canCallMagicSet(string $property) : bool
    {
        return false;
    }

    public function callMagicSet(string $property, $value) : void
    {
        return null;
    }

    public function canCallMagicCall(string $method) : bool
    {
        return false;
    }

    public function callMagicCall(string $method, array $arguments) : void
    {
        return null;
    }
}
