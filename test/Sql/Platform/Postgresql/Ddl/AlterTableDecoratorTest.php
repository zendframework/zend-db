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
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl;
use Zend\Db\Sql\Platform\Postgresql\Ddl\AlterTableDecorator;

class AlterTableDecoratorTest extends \PHPUnit_Framework_TestCase {
    /**
     * @dataProvider tableAlterationsProvider
     */
    public function testGetSqlString(AlterTable $createTable, $expectedSql)
    {
        $alterTableDecorator = new AlterTableDecorator();
        $alterTableDecorator->setSubject($createTable);

        $adapterPlatform = new Postgresql();
        $createTableSql = $this->trimExtraIndents($alterTableDecorator->getSqlString($adapterPlatform));
        $this->assertEquals($expectedSql, $createTableSql);
    }

    public function tableAlterationsProvider()
    {
        $newIdx = new Ddl\Index\Index('field_1', 'new_idx');
        $newField_2 = new Ddl\Column\Varchar('field_2');
        $newField_2->setLength(1024);

        // AlterTable on its own
        $noIndex = new Ddl\AlterTable('no_index');
        $noIndex->addColumn($newField_2);

        $expectedNewFieldNoIndex = 'ALTER TABLE "no_index"' . "\n"
                                 . 'ADD COLUMN "field_2" VARCHAR(1024) NOT NULL;';

        // AlterTable on its own
        $onlyIndex = new Ddl\AlterTable('only_index');
        $onlyIndex->addConstraint($newIdx);

        $expectedOnlyIndex = 'CREATE INDEX "new_idx" ON "only_index"("field_1");';

        // AlterTable with Create Index
        $mixedAddIndex = new Ddl\AlterTable('mixed_index');
        $mixedAddIndex->addColumn($newField_2);
        $mixedAddIndex->addConstraint($newIdx);

        $expectedMixedAddIndex = 'ALTER TABLE "mixed_index"' . "\n"
                               . 'ADD COLUMN "field_2" VARCHAR(1024) NOT NULL;' . "\n"
                               . 'CREATE INDEX "new_idx" ON "mixed_index"("field_1");';

        // Drop Index
        // DROP CONSTRAINT always with DROP INDEX to compensate for dropConstraint()
        // interface only accepting strings, not inspectable object.
        $dropIndex = new Ddl\AlterTable('drop_index');
        $dropIndex->dropConstraint('new_idx');

        $expectedOnlyDropIndex = 'ALTER TABLE "drop_index"' . "\n"
                               . 'DROP CONSTRAINT IF EXISTS "new_idx";' . "\n"
                               . 'DROP INDEX IF EXISTS "new_idx";';

        return [
            [$noIndex,          $expectedNewFieldNoIndex],
            [$onlyIndex,        $expectedOnlyIndex],
            [$mixedAddIndex,    $expectedMixedAddIndex],
            [$dropIndex,        $expectedOnlyDropIndex]
        ];
    }

    private function trimExtraIndents($sqlString) {
        return join("\n", array_map("trim", explode("\n", $sqlString)));
    }
}
