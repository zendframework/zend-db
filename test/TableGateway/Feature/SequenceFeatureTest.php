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
use Zend\Db\Adapter\Platform\Postgresql;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\TableGateway\Feature\SequenceFeature;
use ZendTest\Db\TestAsset\TrustingPostgresqlPlatform;

class SequenceFeatureTest extends PHPUnit_Framework_TestCase
{
    /** @var \Zend\Db\TableGateway\TableGateway */
    protected $tableGateway = null;

    /**  @var string primary key name */
    protected $primaryKeyField = 'id';

    /** @var string  sequence name */
    protected $sequenceName = 'table_sequence';

    /**
     * @dataProvider nextSequenceIdProvider
     */
    public function testNextSequenceIdForNamedSequence($platformName, $statementSql)
    {
        $feature = new SequenceFeature($this->primaryKeyField, $this->sequenceName);
        $feature->setTableGateway($this->tableGateway);
        $feature->nextSequenceId();
    }

    /**
     * @dataProvider tableIdentifierProvider
     */
    public function testSequenceNameGenerated($tableIdentifier, $sequenceName)
    {
        $adapter = $this->getMock('Zend\Db\Adapter\Adapter', ['getPlatform', 'createStatement'], [], '', false);
        $adapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue(new TrustingPostgresqlPlatform()));

        $this->tableGateway = $this->getMockForAbstractClass('Zend\Db\TableGateway\TableGateway', [$tableIdentifier, $adapter], '', true);

        $sequence = new SequenceFeature('serial_column');
        $sequence->setTableGateway($this->tableGateway);
        $sequence->getSequenceName();
    }

    public function testSequenceNameQueriedWhenTooLong()
    {

    }

    /**
     * Sequences for SERIAL columns start with no name which eventually gets filled.
     * Ensure null value is replaced with actual on first call
     * so that repeated calls to getSequenceName() do not make extra database calls (for long name case)
     *
     * Also test do not try to generate when name is manually supplied in constructor.
     */
    public function testCacheSequenceName()
    {

    }

    /**
     * @dataProvider nextSequenceIdProvider
     */
    public function testNextSequenceIdForSerialColumn($platformName, $statementSql)
    {
        $platform = $this->getMockForAbstractClass('Zend\Db\Adapter\Platform\PlatformInterface', ['getName']);
        $platform->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($platformName));
        $platform->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnValue($this->sequenceName));
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

        $feature = new SequenceFeature($this->primaryKeyField, $this->sequenceName);
        $feature->setTableGateway($this->tableGateway);
        $feature->nextSequenceId();
    }

    public function testDoNotReactToDifferentColumnName() {
        $sequence1 = new SequenceFeature('col_1', 'seq_1');
        $this->assertEquals($sequence1->lastSequenceId('col_2'), null, 'Sequence should not react to foreign column name');
        $this->assertEquals($sequence1->nextSequenceId('col_2'), null, 'Sequence should not react to foreign column name');
    }

    public function nextSequenceIdProvider()
    {
        return [['PostgreSQL', 'SELECT NEXTVAL(\'"' . $this->sequenceName . '"\')'],
            ['Oracle', 'SELECT ' . $this->sequenceName . '.NEXTVAL as "nextval" FROM dual']];
    }

    public function tableIdentifierProvider()
    {
        return [
            ['table', 'table_serial_column_seq'],
            [['schema', 'table'], '"schema"."table_serial_column_seq"'],
            [new TableIdentifier('table', 'schema'), '"schema"."table_serial_column_seq"']
        ];
    }
}
