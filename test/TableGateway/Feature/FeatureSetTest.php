<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\TableGateway\Feature;

use ReflectionClass;
use Zend\Db\TableGateway\Feature\AbstractFeature;
use Zend\Db\TableGateway\Feature\FeatureSet;
use Zend\Db\TableGateway\Feature\MasterSlaveFeature;
use Zend\Db\TableGateway\Feature\SequenceFeature;
use Zend\Db\TableGateway\Feature\MetadataFeature;
use Zend\Db\Metadata\Object\ConstraintObject;

class FeatureSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover FeatureSet::addFeature
     * @group ZF2-4993
     */
    public function testAddFeatureThatFeatureDoesNotHaveTableGatewayButFeatureSetHas()
    {
        $mockMasterAdapter = $this->getMock('Zend\Db\Adapter\AdapterInterface');

        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue(
            $mockStatement
        ));
        $mockMasterAdapter->expects($this->any())->method('getDriver')->will($this->returnValue($mockDriver));
        $mockMasterAdapter->expects($this->any())->method('getPlatform')->will($this->returnValue(new \Zend\Db\Adapter\Platform\Sql92()));

        $mockSlaveAdapter = $this->getMock('Zend\Db\Adapter\AdapterInterface');

        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue(
            $mockStatement
        ));
        $mockSlaveAdapter->expects($this->any())->method('getDriver')->will($this->returnValue($mockDriver));
        $mockSlaveAdapter->expects($this->any())->method('getPlatform')->will($this->returnValue(new \Zend\Db\Adapter\Platform\Sql92()));

        $tableGatewayMock = $this->getMockForAbstractClass('Zend\Db\TableGateway\AbstractTableGateway');

        //feature doesn't have tableGateway, but FeatureSet has
        $feature = new MasterSlaveFeature($mockSlaveAdapter);

        $featureSet = new FeatureSet;
        $featureSet->setTableGateway($tableGatewayMock);

        $this->assertInstanceOf('Zend\Db\TableGateway\Feature\FeatureSet', $featureSet->addFeature($feature));
    }

    /**
     * @cover FeatureSet::addFeature
     * @group ZF2-4993
     */
    public function testAddFeatureThatFeatureHasTableGatewayButFeatureSetDoesNotHave()
    {
        $tableGatewayMock = $this->getMockForAbstractClass('Zend\Db\TableGateway\AbstractTableGateway');

        $metadataMock = $this->getMock('Zend\Db\Metadata\MetadataInterface');
        $metadataMock->expects($this->any())->method('getColumnNames')->will($this->returnValue(['id', 'name']));

        $constraintObject = new ConstraintObject('id_pk', 'table');
        $constraintObject->setColumns(['id']);
        $constraintObject->setType('PRIMARY KEY');

        $metadataMock->expects($this->any())->method('getConstraints')->will($this->returnValue([$constraintObject]));

        //feature have tableGateway, but FeatureSet doesn't has
        $feature = new MetadataFeature($metadataMock);
        $feature->setTableGateway($tableGatewayMock);

        $featureSet = new FeatureSet;
        $this->assertInstanceOf('Zend\Db\TableGateway\Feature\FeatureSet', $featureSet->addFeature($feature));
    }

    /**
     * @covers Zend\Db\TableGateway\Feature\FeatureSet::getFeatureByClassName
     */
    public function testGetSingleFeatureByClassName()
    {
        $featureMock = $this->getMock(AbstractFeature::class);

        $featureSet = new FeatureSet();
        $featureSet->addFeature($featureMock);

        $this->assertInstanceOf(
            get_class($featureMock),
            $featureSet->getFeatureByClassName(get_class($featureMock)),
            "When only one feature of its type is added to FeatureSet, getFeatureByClassName() should return that single instance"
        );
    }

    /**
     * @covers Zend\Db\TableGateway\Feature\FeatureSet::getFeatureByClassName
     */
    public function testGetAllFeaturesOfSameTypeByClassName()
    {
        $featureMock1 = $this->getMock(AbstractFeature::class);
        $featureMock2 = $this->getMock(AbstractFeature::class);

        $featureSet = new FeatureSet();
        $featureSet->addFeature($featureMock1);
        $featureSet->addFeature($featureMock2);

        $features = $featureSet->getFeatureByClassName(get_class($featureMock1));

        $this->assertTrue(is_array($features), "When multiple features of same type are added, they all should be return in array");

        $this->assertInstanceOf(get_class($featureMock1), $features[0]);
        $this->assertInstanceOf(get_class($featureMock2), $features[1]);
    }

    /**
     * @covers Zend\Db\TableGateway\Feature\FeatureSet::getFeatureByClassName
     */
    public function testGetFeatureByClassNameReturnsFalseIfNotAdded()
    {
        $featureMock = $this->getMock(AbstractFeature::class);

        $featureSet = new FeatureSet();

        $this->assertFalse(
            $featureSet->getFeatureByClassName(get_class($featureMock)),
            "Requesting unregistered feature should return false"
        );
    }

    /**
     * @covers Zend\Db\TableGateway\Feature\FeatureSet::canCallMagicCall
     */
    public function testCanCallMagicCallReturnsTrueForAddedMethodOfAddedFeature()
    {
        $feature = new SequenceFeature('id', 'table_sequence');
        $featureSet = new FeatureSet;
        $featureSet->addFeature($feature);

        $this->assertTrue(
            $featureSet->canCallMagicCall('lastSequenceId'),
            "Should have been able to call lastSequenceId from the Sequence Feature"
        );
    }

    /**
     * @covers Zend\Db\TableGateway\Feature\FeatureSet::canCallMagicCall
     */
    public function testCanCallMagicCallReturnsFalseForAddedMethodOfAddedFeature()
    {
        $feature = new SequenceFeature('id', 'table_sequence');
        $featureSet = new FeatureSet;
        $featureSet->addFeature($feature);

        $this->assertFalse(
            $featureSet->canCallMagicCall('postInitialize'),
            "Should have been able to call postInitialize from the MetaData Feature"
        );
    }

    /**
     * @covers Zend\Db\TableGateway\Feature\FeatureSet::canCallMagicCall
     */
    public function testCanCallMagicCallReturnsFalseWhenNoFeaturesHaveBeenAdded()
    {
        $featureSet = new FeatureSet;
        $this->assertFalse(
            $featureSet->canCallMagicCall('lastSequenceId')
        );
    }

    /**
     * @covers Zend\Db\TableGateway\Feature\FeatureSet::callMagicCall
     */
    public function testCallMagicCallSucceedsForValidMethodOfAddedFeature()
    {
        $featureSet = new FeatureSet;
        $featureSet->addFeature($this->getMockSequence('table_name', 'sequence_name', 1));
        $this->assertEquals(1, $featureSet->callMagicCall('lastSequenceId', null));
    }

    /**
     * @covers Zend\Db\TableGateway\Feature\FeatureSet::callMagicCall
     */
    public function testCallMagicMethodAllSimilarFeaturesUntilNotNull()
    {
        $featureSet = new FeatureSet();

        $featureSet->addFeature($this->getMockSequence('col_1', 'seq_1', 1));
        $featureSet->addFeature($this->getMockSequence('col_2', 'seq_2', 2));
        $featureSet->addFeature($this->getMockSequence('col_3', 'seq_3', 3));

        $result = $featureSet->callMagicCall('lastSequenceId', 'col_2');

        $this->assertEquals(2, $result);
    }

    // FeatureSet uses method_exists which does not work on mock objects. Therefore, need a real object.
    private function getMockSequence($columnName, $sequenceName, $expectedCurrVal)
    {
        $platformMock = $this->getMock('Zend\Db\Adapter\Platform\Postgresql');
        $platformMock->expects($this->any())
            ->method('getName')->will($this->returnValue('PostgreSQL'));

        $resultMock = $this->getMock('Zend\Db\Adapter\Driver\Pgsql\Result');
        $resultMock->expects($this->any())
            ->method('current')
            ->will($this->returnValue(['currval' => $expectedCurrVal]));

        $statementMock = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $statementMock->expects($this->any())
            ->method('prepare');
        $statementMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($resultMock));

        $adapterMock = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock->expects($this->any())
            ->method('getPlatform')->will($this->returnValue($platformMock));
        $adapterMock->expects($this->any())
            ->method('createStatement')->will($this->returnValue($statementMock));

        $tableGatewayMock = $this->getMockBuilder('Zend\Db\TableGateway\AbstractTableGateway')
            ->disableOriginalConstructor()
            ->getMock();

        $reflectionClass = new ReflectionClass('Zend\Db\TableGateway\AbstractTableGateway');
        $reflectionProperty = $reflectionClass->getProperty('adapter');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($tableGatewayMock, $adapterMock);

        $feature = new SequenceFeature($columnName, $sequenceName);
        $feature->setTableGateway($tableGatewayMock);

        return $feature;
    }
}
