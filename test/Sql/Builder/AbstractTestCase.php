<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Adapter;
use ZendTest\Db\TestAsset;
use Zend\Db\Sql\Builder;

/**
 * @method \Zend\Db\Sql\Select  select(null|string $table)
 * @method \Zend\Db\Sql\Update  update(null|string $table)
 * @method \Zend\Db\Sql\Delete  delete(null|string $table)
 * @method \Zend\Db\Sql\Insert  insert(null|string $table)
 * @method \Zend\Db\Sql\Combine combine(null|string $table)
 *
 * @method \Zend\Db\Sql\Ddl\DropTable     dropTable(null|string $table)
 * @method \Zend\Db\Sql\Ddl\AlterTable    alterTable(null|string $table)
 * @method \Zend\Db\Sql\Ddl\CreateTable   createTable(null|string $table)
 * @method \Zend\Db\Sql\Ddl\Column\Column createColumn(null|string $name)
 *
 * @method \Zend\Db\Sql\Expression             expression($expression = '', $parameters = null, array $types = []);
 *
 * @method \Zend\Db\Sql\Predicate\Between      predicate_Between($identifier = null, $minValue = null, $maxValue = null)
 * @method \Zend\Db\Sql\Predicate\NotBetween   predicate_NotBetween($identifier = null, $minValue = null, $maxValue = null)
 * @method \Zend\Db\Sql\Predicate\Expression   predicate_Expression($expression = null, $valueParameter = null);
 * @method \Zend\Db\Sql\Predicate\In           predicate_In($identifier = null, $valueSet = null);
 * @method \Zend\Db\Sql\Predicate\IsNotNull    predicate_IsNotNull($identifier = null);
 * @method \Zend\Db\Sql\Predicate\IsNull       predicate_IsNull($identifier = null);
 * @method \Zend\Db\Sql\Predicate\Like         predicate_Like($identifier = null, $like = null);
 * @method \Zend\Db\Sql\Predicate\Literal      predicate_Literal($literal = '');
 * @method \Zend\Db\Sql\Predicate\NotIn        predicate_NotIn($identifier = null, $valueSet = null);
 * @method \Zend\Db\Sql\Predicate\NotLike      predicate_NotLike($identifier = null, $like = null);
 * @method \Zend\Db\Sql\Predicate\Operator     predicate_Operator($arg0, $arg1, $arg2);
 * @method \Zend\Db\Sql\Predicate\Predicate    predicate_Predicate($arg0, $arg1);
 * @method \Zend\Db\Sql\Predicate\PredicateSet predicate_PredicateSet($arg0, $arg1);
 *
 * @method \Zend\Db\Sql\Ddl\Index\Index index_Index($arg0, $arg1, $arg2);
 *
 * @method \Zend\Db\Sql\Ddl\Constraint\Check      constraint_Check     ($expression, $name);
 * @method \Zend\Db\Sql\Ddl\Constraint\ForeignKey constraint_ForeignKey($name, $columns, $referenceTable, $referenceColumn, $onDeleteRule = null, $onUpdateRule = null);
 * @method \Zend\Db\Sql\Ddl\Constraint\PrimaryKey constraint_PrimaryKey($columns = null, $name = null);
 * @method \Zend\Db\Sql\Ddl\Constraint\UniqueKey  constraint_UniqueKey ($columns = null, $name = null);
 *
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\BigInteger  column_BigInteger ($name = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Binary      column_Binary     ($name, $length = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Blob        column_Blob       ($name, $length = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Boolean     column_Boolean    ($name = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Char        column_Char       ($name, $length = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Column      column_Column     ($name = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Date        column_Date       ($name = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Datetime    column_Datetime   ($name = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Decimal     column_Decimal    ($name, $digits = null, $decimal = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Floating    column_Floating   ($name, $digits = null, $decimal = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Integer     column_Integer    ($name = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Text        column_Text       ($name, $length = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Time        column_Time       ($name = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Timestamp   column_Timestamp  ($name = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Varbinary   column_Varbinary  ($name, $length = null, $nullable = false, $default = null, array $options = array());
 * @method \ZendTest\Db\Sql\Builder\Ddl\Column\Varchar     column_Varchar    ($name, $length = null, $nullable = false, $default = null, array $options = array());
 */
class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    protected $adapters = [];
    protected $useNamedParameters = false;

    protected function prepareDataProvider($data)
    {
        if (func_num_args() >= 2) {
            foreach (func_get_args() as $i=>$arg) {
                if ($i == 0) {
                    continue;
                }
                foreach ($arg as $a) {
                    $data[] = $a;
                }
            }
        }
        $res = [];
        foreach ($data as $index => $test) {
            foreach ($test['expected'] as $platform => $expected) {
                $res[$index . '->' . $platform] = [
                    'sqlObject' => $test['sqlObject'],
                    'platform'  => $platform,
                    'expected'  => $expected,
                ];
            }
        }
        return $res;
    }

    public function assertBuilder($sqlObject, $platform = null, $expected = null)
    {
        if ($sqlObject instanceof \Closure) {
            $sqlObject = call_user_func($sqlObject);
        }
        $builder = new Builder\Builder();
        $adapter = $this->getAdapterForPlatform($platform);

        if (is_array($expected) && isset($expected['decorators'])) {
            foreach ($expected['decorators'] as $type=>$decorator) {
                $builder->setPlatformBuilder($platform, $type, $this->resolveDecorator($decorator, $builder));
            }
        }
        if (is_array($expected) && isset($expected['ExpectedException'])) {
            if (is_array($expected['ExpectedException'])) {
                $this->setExpectedException(
                    $expected['ExpectedException'][0],
                    isset($expected['ExpectedException'][1]) ? $expected['ExpectedException'][1] : '',
                    isset($expected['ExpectedException'][2]) ? $expected['ExpectedException'][2] : null
                );
            } else {
                $this->setExpectedException($expected['ExpectedException']);
            }
        }

        $expectedString = is_string($expected) ? $expected : (isset($expected['string']) ? $expected['string'] : null);
        if ($expectedString) {
            $actual = $builder->getSqlString($sqlObject, $adapter);
            $this->assertEquals($expectedString, $actual, "getSqlString()");
        }
        if (is_array($expected) && isset($expected['prepare'])) {
            if ($expected['prepare'] === true) {
                $expected['prepare'] = $expectedString;
            }
            $this->useNamedParameters = isset($expected['useNamedParams']) && $expected['useNamedParams'];

            $actual = $builder->prepareStatement($sqlObject, $adapter);
            $this->assertEquals($expected['prepare'], $actual->getSql(), "prepareStatement()");
            if (isset($expected['parameters'])) {
                $this->assertSame(
                    $expected['parameters'],
                    $actual->getParameterContainer()->getNamedArray(),
                    "parameterContainer()"
                );
            } elseif (isset($expected['parametersEquals'])) {
                $this->assertEquals(
                    $expected['parametersEquals'],
                    $actual->getParameterContainer()->getNamedArray(),
                    "parameterContainer()"
                );
            }
        }
    }

    protected function getAdapterForPlatform($platformName)
    {
        $platformName = str_replace(' ', '', strtolower($platformName));
        if (isset($this->adapters[$platformName])) {
            return $this->adapters[$platformName];
        }

        switch ($platformName) {
            case 'sql92'     : $platform  = new TestAsset\TrustingSql92Platform();     break;
            case 'mysql'     : $platform  = new TestAsset\TrustingMysqlPlatform();     break;
            case 'oracle'    : $platform  = new TestAsset\TrustingOraclePlatform();    break;
            case 'sqlserver' : $platform  = new TestAsset\TrustingSqlServerPlatform(); break;
            case 'ibmdb2'    : $platform  = new TestAsset\TrustingIbmDb2Platform(); break;
            case 'sqlite'    : $platform  = new TestAsset\TrustingSqlitePlatform(); break;
            default : $platform = null;
        }

        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnCallback(
            function ($name) { return (($this->useNamedParameters) ? ':' . $name : '?'); }
        ));
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnCallback(function () {
            $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
            $setSql = $this->returnCallback(function ($sql = null) {
                static $thisSql;
                if ($sql === null) {
                    return $thisSql;
                }
                $thisSql = $sql;
            });
            $setParameterContainer = $this->returnCallback(function ($parameterContainer = null) {
                static $thisParameterContainer;
                if ($parameterContainer === null) {
                    return $thisParameterContainer;
                }
                $thisParameterContainer = $parameterContainer;
            });
            $mockStatement->expects($this->any())->method('setSql')->will($setSql);
            $mockStatement->expects($this->any())->method('getSql')->will($setSql);
            $mockStatement->expects($this->any())->method('setParameterContainer')->will($setParameterContainer);
            $mockStatement->expects($this->any())->method('getParameterContainer')->will($setParameterContainer);
            return $mockStatement;
        }));

        $this->adapters[$platformName] = new Adapter\Adapter($mockDriver, $platform);
        return $this->adapters[$platformName];
    }

    protected function resolveDecorator($decorator, $builder)
    {
        if (!is_array($decorator)) {
            return $decorator;
        }
        $decoratorMock = $this->getMock($decorator[0], ['build'], [$builder]);
        $decoratorMock->expects($this->any())->method('build')->will($this->returnValue([$decorator[1]]));
        return $decoratorMock;
    }

    public function __call($name, $arguments)
    {
        $aliasMap = [
            'select'                => 'Zend\Db\Sql\Select',
            'delete'                => 'Zend\Db\Sql\Delete',
            'update'                => 'Zend\Db\Sql\Update',
            'insert'                => 'Zend\Db\Sql\Insert',
            'combine'               => 'Zend\Db\Sql\Combine',
            'dropTable'             => 'Zend\Db\Sql\Ddl\DropTable',
            'alterTable'            => 'Zend\Db\Sql\Ddl\AlterTable',
            'createTable'           => 'Zend\Db\Sql\Ddl\CreateTable',
            'createColumn'          => 'Zend\Db\Sql\Ddl\Column\Column',

            'expression'            => 'Zend\Db\Sql\Expression',

            'predicate_Between'     => 'Zend\Db\Sql\Predicate\Between',
            'predicate_NotBetween'  => 'Zend\Db\Sql\Predicate\NotBetween',
            'predicate_Expression'  => 'Zend\Db\Sql\Predicate\Expression',
            'predicate_In'          => 'Zend\Db\Sql\Predicate\In',
            'predicate_IsNotNull'   => 'Zend\Db\Sql\Predicate\IsNotNull',
            'predicate_IsNull'      => 'Zend\Db\Sql\Predicate\IsNull',
            'predicate_Like'        => 'Zend\Db\Sql\Predicate\Like',
            'predicate_Literal'     => 'Zend\Db\Sql\Predicate\Literal',
            'predicate_NotIn'       => 'Zend\Db\Sql\Predicate\NotIn',
            'predicate_NotLike'     => 'Zend\Db\Sql\Predicate\NotLike',
            'predicate_Operator'    => 'Zend\Db\Sql\Predicate\Operator',
            'predicate_Predicate'   => 'Zend\Db\Sql\Predicate\Predicate',
            'predicate_PredicateSet'=> 'Zend\Db\Sql\Predicate\PredicateSet',

            'index_Index'           => 'Zend\Db\Sql\Ddl\Index\Index',

            'constraint_Check'      => 'Zend\Db\Sql\Ddl\Constraint\Check',
            'constraint_ForeignKey' => 'Zend\Db\Sql\Ddl\Constraint\ForeignKey',
            'constraint_PrimaryKey' => 'Zend\Db\Sql\Ddl\Constraint\PrimaryKey',
            'constraint_UniqueKey'  => 'Zend\Db\Sql\Ddl\Constraint\UniqueKey',
            'column_BigInteger'     => 'Zend\Db\Sql\Ddl\Column\BigInteger',
            'column_Binary'         => 'Zend\Db\Sql\Ddl\Column\Binary',
            'column_Blob'           => 'Zend\Db\Sql\Ddl\Column\Blob',
            'column_Boolean'        => 'Zend\Db\Sql\Ddl\Column\Boolean',
            'column_Char'           => 'Zend\Db\Sql\Ddl\Column\Char',
            'column_Column'         => 'Zend\Db\Sql\Ddl\Column\Column',
            'column_Date'           => 'Zend\Db\Sql\Ddl\Column\Date',
            'column_Datetime'       => 'Zend\Db\Sql\Ddl\Column\Datetime',
            'column_Decimal'        => 'Zend\Db\Sql\Ddl\Column\Decimal',
            'column_Floating'       => 'Zend\Db\Sql\Ddl\Column\Floating',
            'column_Integer'        => 'Zend\Db\Sql\Ddl\Column\Integer',
            'column_Text'           => 'Zend\Db\Sql\Ddl\Column\Text',
            'column_Time'           => 'Zend\Db\Sql\Ddl\Column\Time',
            'column_Timestamp'      => 'Zend\Db\Sql\Ddl\Column\Timestamp',
            'column_Varbinary'      => 'Zend\Db\Sql\Ddl\Column\Varbinary',
            'column_Varchar'        => 'Zend\Db\Sql\Ddl\Column\Varchar',
        ];
        if (!isset($aliasMap[$name])) {
            throw new \Exception($name . ' method not found');
        }
        $refl = new \ReflectionClass($aliasMap[$name]);
        return $refl->newInstanceArgs($arguments);
    }
}
