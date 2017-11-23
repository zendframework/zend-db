<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\TableGateway\Feature;

use PHPUnit_Framework_TestCase;
use Zend\Db\Adapter\Platform\PlatformInterface;
use ZendTest\Db\TestAsset;
use Zend\Db\TableGateway\Feature\SequenceFeature;

class SequenceFeatureTest extends PHPUnit_Framework_TestCase
{
    /** @var \Zend\Db\TableGateway\TableGateway */
    protected $tableGateway = null;

    /**  @var string primary key name */
    protected $primaryKeyField = 'id';

    /**
     * @dataProvider nextSequenceIdProvider
     */
    public function testNextSequenceId($platformName, $sequenceName, $statementSql)
    {
        $feature = new SequenceFeature($this->primaryKeyField, $sequenceName);

        $platform = $this->getPlatformStub($platformName);
        $adapter = $this->getMock('Zend\Db\Adapter\Adapter', ['getPlatform', 'createStatement'], [], '', false);
        $adapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue($platform));
        $result = $this->getMockForAbstractClass('Zend\Db\Adapter\Driver\ResultInterface', [], '', false, true, true, ['current']);
        $result->expects($this->any())
            ->method('current')
            ->will($this->returnValue(['nextval' => 2]));
        $statement = $this->getMockForAbstractClass('Zend\Db\Adapter\Driver\StatementInterface', [], '', false, true, true, ['prepare', 'execute']);
        $statement->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($result));
        $statement->expects($this->any())
            ->method('prepare')
            ->with($statementSql);
        $adapter->expects($this->once())
            ->method('createStatement')
            ->will($this->returnValue($statement));
        $this->tableGateway = $this->getMockForAbstractClass('Zend\Db\TableGateway\TableGateway', ['table', $adapter], '', true);
        $feature->setTableGateway($this->tableGateway);
        $feature->nextSequenceId();
    }

    public function nextSequenceIdProvider()
    {
        return [
            //@TODO MS SQL SERVER 2016 now supports sequences too
            ['PostgreSQL', 'table_sequence',            'SELECT NEXTVAL(\'"table_sequence"\')'],
            ['PostgreSQL', ['schema','table_sequence'], 'SELECT NEXTVAL(\'"schema"."table_sequence"\')'],
            ['Oracle',     'table_sequence',            'SELECT "table_sequence".NEXTVAL as "nextval" FROM dual']
        ];
    }

    /**
     * Data provider
     * @TODO this method is replicated in a several tests. Seems common enough to put in common utility, trait or abstract test class
     *
     * @param string $platform
     *
     * @return PlatformInterface
     */
    protected function getPlatformStub($platform)
    {
        switch ($platform) {
            case 'Oracle'     : $platform = new TestAsset\TrustingOraclePlatform();    break;
            case 'PostgreSQL' : $platform = new TestAsset\TrustingPostgresqlPlatform(); break;
            default : $platform = null;
        }

        return $platform;
    }
}
