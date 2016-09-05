<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Postgresql\Ddl;


use Zend\Db\Adapter\Platform\Postgresql;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl;

class CreateTableDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox integration test: Testing CreateTableDecorator will use CreateTable as an internal state to adjust Index creation to be a separate statement
     * @covers Zend\Db\Sql\Platform\Postgresql\Ddl\CreateTableDecorator::setSubject
     * @covers Zend\Db\Sql\Platform\Postgresql\Ddl\CreateTableDecorator::buildSqlString
     * @covers Zend\Db\Sql\Platform\Postgresql\Ddl\CreateTableDecorator::processIndexes
     * @covers Zend\Db\Sql\Platform\Postgresql\Ddl\CreateTableDecorator::processStatementEnd
     * @dataProvider tableDefinitionsProvider
     */
    public function testGetSqlString(CreateTable $createTable, $expectedSql)
    {
        $createTableDecorator = new CreateTableDecorator();
        $createTableDecorator->setSubject($createTable);

        $createTableSql = $createTableDecorator->getSqlString(new Postgresql());
        $this->assertEquals($expectedSql, $createTableSql);
    }

    public function tableDefinitionsProvider()
    {
        $id = new Ddl\Column\Integer('id', false, null);
        $name = new Ddl\Column\Varchar('username', false, null);
        $name->setLength(1024);
        $nameUnique = new Ddl\Constraint\UniqueKey('username');

        $idIndex = new Ddl\Index\Index('id', 'id_idx');
        $nameIndex = new Ddl\Index\Index('username', 'username_index');

        // CREATE TABLE only.
        $columnsOnly = new CreateTable('columns_only');
        $columnsOnly->addColumn($id);
        $columnsOnly->addColumn($name);

        $expectedColumnsOnly = 'CREATE TABLE "columns_only" ( ' . "\n"
                             . '    "id" INTEGER NOT NULL,' . "\n"
                             . '    "username" VARCHAR(1024) NOT NULL ' . "\n"
                             . ');';

        // CreateTable with Create Index
        $withSingleIndex = new CreateTable('with_single_index');
        $withSingleIndex->addColumn($id);
        $withSingleIndex->addColumn($name);
        $withSingleIndex->addConstraint($idIndex);

        $expectedWithSingleInstance = 'CREATE TABLE "with_single_index" ( ' . "\n"
                                    . '    "id" INTEGER NOT NULL,' . "\n"
                                    . '    "username" VARCHAR(1024) NOT NULL ' . "\n"
                                    . '); ' . "\n"
                                    . 'CREATE INDEX "id_idx" ON "with_single_index"("id");';


        // Mixed handling of index separation from constraints.
        $mixed = new CreateTable('mixed');
        $mixed->addColumn($id);
        $mixed->addColumn($name);
        $mixed->addConstraint($nameUnique);
        $mixed->addConstraint($idIndex);
        $mixed->addConstraint($nameIndex);

        $expectedMixed = 'CREATE TABLE "mixed" ( ' . "\n"
                       . '    "id" INTEGER NOT NULL,' . "\n"
                       . '    "username" VARCHAR(1024) NOT NULL , ' . "\n"
                       . '    UNIQUE ("username") ' . "\n"
                       . '); ' . "\n"
                       . 'CREATE INDEX "id_idx" ON "mixed"("id");' . "\n"
                       . 'CREATE INDEX "username_index" ON "mixed"("username");';

        return [
            [$columnsOnly,      $expectedColumnsOnly],
            [$withSingleIndex,  $expectedWithSingleInstance],
            [$mixed,            $expectedMixed],
        ];
    }

}
