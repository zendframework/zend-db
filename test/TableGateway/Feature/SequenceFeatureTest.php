<?php

/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\TableGateway\Feature;

use PHPUnit_Framework_TestCase;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\TableGateway\Feature\SequenceFeature;
use ZendTest\Db\TestAsset\TrustingOraclePlatform;
use ZendTest\Db\TestAsset\TrustingPostgresqlPlatform;

class SequenceFeatureTest extends PHPUnit_Framework_TestCase
{
    /** @var \Zend\Db\TableGateway\TableGateway */
    protected $tableGateway = null;

    /**  @var string primary key name */
    protected $primaryKeyField = 'id';

    /** @var string  sequence name */
    protected $sequenceName = 'sequence_name';

    /**
     * @dataProvider identifierProvider
     */
    public function testSequenceNameGenerated($platform, $tableIdentifier, $sequenceName, $expectedSequenceName)
    {
        $adapter = $this->getMock('Zend\Db\Adapter\Adapter', ['getPlatform', 'createStatement'], [], '', false);
        $adapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue($platform));

        $this->tableGateway = $this->getMockForAbstractClass('Zend\Db\TableGateway\TableGateway', [$tableIdentifier, $adapter], '', true);

        $sequence = new SequenceFeature('serial_column', $sequenceName);
        $sequence->setTableGateway($this->tableGateway);
        $this->assertEquals($expectedSequenceName, $sequence->getSequenceName());
    }

    public function identifierProvider()
    {
        return [
            [new TrustingPostgresqlPlatform(),
                'table',                                null,                        '"table_serial_column_seq"', ],
            [new TrustingPostgresqlPlatform(),
                ['schema', 'table'],                    null,                        '"schema"."table_serial_column_seq"', ],
            [new TrustingPostgresqlPlatform(),
                new TableIdentifier('table', 'schema'), null,                        '"schema"."table_serial_column_seq"', ],
            [new TrustingPostgresqlPlatform(),
                new TableIdentifier('table', 'schema'), ['schema', 'sequence_name'], '"schema"."sequence_name"', ],
        ];
    }

    public function testSequenceNameQueriedWhenTooLong()
    {
        $adapter = $this->getMock('Zend\Db\Adapter\Adapter', ['getPlatform', 'createStatement'], [], '', false);
        $adapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue(new TrustingPostgresqlPlatform()));
        $result = $this->getMockForAbstractClass('Zend\Db\Adapter\Driver\ResultInterface', [], '', false, true, true, ['current']);
        $result->expects($this->any())
            ->method('current')
            ->will($this->returnValue(['pg_get_serial_sequence' => 'table_name_column_very_long_name_causing_postgresql_to_trun_seq']));
        $statement = $this->getMockForAbstractClass('Zend\Db\Adapter\Driver\StatementInterface', [], '', false, true, true, ['prepare', 'execute']);
        $statement->expects($this->any())
            ->method('execute')
            ->with(['table' => 'table_name', 'column' => 'column_very_long_name_causing_postgresql_to_truncate'])
            ->will($this->returnValue($result));
        $statement->expects($this->any())
            ->method('prepare')
            ->with('SELECT pg_get_serial_sequence(:table, :column)');
        $adapter->expects($this->once())
            ->method('createStatement')
            ->will($this->returnValue($statement));
        $this->tableGateway = $this->getMockForAbstractClass('Zend\Db\TableGateway\TableGateway', ['table_name', $adapter], '', true);

        $sequence = new SequenceFeature('column_very_long_name_causing_postgresql_to_truncate');
        $sequence->setTableGateway($this->tableGateway);

        $this->assertEquals('table_name_column_very_long_name_causing_postgresql_to_trun_seq', $sequence->getSequenceName());
    }

    /**
     * Sequences for SERIAL columns start with no name which eventually gets filled.
     * Ensure null value is replaced with actual on first call
     * so that repeated calls to getSequenceName() do not make extra database calls (for long name case).
     *
     * Also test do not try to generate when name is manually supplied in constructor.
     */
    public function testCacheSequenceName()
    {
        $adapter = $this->getMock('Zend\Db\Adapter\Adapter', ['getPlatform', 'createStatement'], [], '', false);
        $adapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue(new TrustingPostgresqlPlatform()));
        $result = $this->getMockForAbstractClass('Zend\Db\Adapter\Driver\ResultInterface', [], '', false, true, true, ['current']);
        $result->expects($this->once())
            ->method('current')
            ->will($this->returnValue(['pg_get_serial_sequence' => 'table_name_column_very_long_name_causing_postgresql_to_trun_seq']));
        $statement = $this->getMockForAbstractClass('Zend\Db\Adapter\Driver\StatementInterface', [], '', false, true, true, ['prepare', 'execute']);
        $statement->expects($this->once())
            ->method('execute')
            ->with(['table' => 'table_name', 'column' => 'column_very_long_name_causing_postgresql_to_truncate'])
            ->will($this->returnValue($result));
        $statement->expects($this->once())
            ->method('prepare')
            ->with('SELECT pg_get_serial_sequence(:table, :column)');
        $adapter->expects($this->once())
            ->method('createStatement')
            ->will($this->returnValue($statement));
        $this->tableGateway = $this->getMockForAbstractClass('Zend\Db\TableGateway\TableGateway', ['table_name', $adapter], '', true);

        $sequence = new SequenceFeature('column_very_long_name_causing_postgresql_to_truncate');
        $sequence->setTableGateway($this->tableGateway);

        $this->assertEquals('table_name_column_very_long_name_causing_postgresql_to_trun_seq', $sequence->getSequenceName());
        $this->assertEquals('table_name_column_very_long_name_causing_postgresql_to_trun_seq', $sequence->getSequenceName());
    }

    /**
     * @dataProvider nextSequenceIdProvider
     */
    public function testNextSequenceIdByPlatform($platform, $statementSql, $statementParameter)
    {
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
            ->with($statementParameter)
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

    public function nextSequenceIdProvider()
    {
        return [
            [new TrustingPostgresqlPlatform(), 'SELECT NEXTVAL( :sequence_name )', ['sequence_name' => $this->sequenceName]],
            [new TrustingOraclePlatform(),     'SELECT "'.$this->sequenceName.'".NEXTVAL as "nextval" FROM dual', []],
        ];
    }
    /**
     * @dataProvider lastSequenceIdProvider
     */
    public function testLastSequenceIdByPlatform($platform, $statementSql, $statementParameter)
    {
        $adapter = $this->getMock('Zend\Db\Adapter\Adapter', ['getPlatform', 'createStatement'], [], '', false);
        $adapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue($platform));
        $result = $this->getMockForAbstractClass('Zend\Db\Adapter\Driver\ResultInterface', [], '', false, true, true, ['current']);
        $result->expects($this->any())
            ->method('current')
            ->will($this->returnValue(['currval' => 1]));
        $statement = $this->getMockForAbstractClass('Zend\Db\Adapter\Driver\StatementInterface', [], '', false, true, true, ['prepare', 'execute']);
        $statement->expects($this->any())
            ->method('execute')
            ->with($statementParameter)
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
        $feature->lastSequenceId();
    }

    public function lastSequenceIdProvider()
    {
        return [
            [new TrustingPostgresqlPlatform(), 'SELECT CURRVAL( :sequence_name )', ['sequence_name' => $this->sequenceName]],
            [new TrustingOraclePlatform(),     'SELECT "'.$this->sequenceName.'".CURRVAL as "currval" FROM dual', []],
        ];
    }

    public function testDoNotReactToDifferentColumnName()
    {
        $sequence1 = new SequenceFeature('col_1', 'seq_1');
        $this->assertEquals($sequence1->lastSequenceId('col_2'), null, 'Sequence should not react to foreign column name');
        $this->assertEquals($sequence1->nextSequenceId('col_2'), null, 'Sequence should not react to foreign column name');
    }
}
