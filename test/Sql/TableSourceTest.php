<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\TableSource;
use Zend\Db\Sql\TableIdentifier;

class TableSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $t = TableSource::factory(['schema', 'table'], 'alias');
        $this->assertEquals(
            ['schema', 'table', 'alias'],
            [$t->getSource()->getSchema(), $t->getSource()->getTable(), $t->getAlias()]
        );

        $t = TableSource::factory(['alias'=>['schema', 'table']]);
        $this->assertEquals(
            ['schema', 'table', 'alias'],
            [$t->getSource()->getSchema(), $t->getSource()->getTable(), $t->getAlias()]
        );

        $t = TableSource::factory(['alias'=>'table']);
        $this->assertEquals(
            [null, 'table', 'alias'],
            [$t->getSource()->getSchema(), $t->getSource()->getTable(), $t->getAlias()]
        );

        $t = TableSource::factory(new TableIdentifier('table', 'schema'));
        $this->assertEquals(
            ['schema', 'table', null],
            [$t->getSource()->getSchema(), $t->getSource()->getTable(), $t->getAlias()]
        );

        $t = TableSource::factory(['alias' => new TableIdentifier('table', 'schema')]);
        $this->assertEquals(
            ['schema', 'table', 'alias'],
            [$t->getSource()->getSchema(), $t->getSource()->getTable(), $t->getAlias()]
        );

        $t = TableSource::factory(['schema', 'table']);
        $this->assertEquals(
            ['schema', 'table', null],
            [$t->getSource()->getSchema(), $t->getSource()->getTable(), $t->getAlias()]
        );

        $t = TableSource::factory('table');
        $this->assertEquals(
            [null, 'table', null],
            [$t->getSource()->getSchema(), $t->getSource()->getTable(), $t->getAlias()]
        );

        $select = new Select();
        $t = TableSource::factory(['alias'=>$select]);
        $this->assertEquals(
            [null, $select, 'alias'],
            [null, $t->getSource(), $t->getAlias()]
        );

        $t = TableSource::factory($select);
        $this->assertEquals(
            [null, $select, null],
            [null, $t->getSource(), $t->getAlias()]
        );

        $expression = new Expression('psql_function_which_returns_table');
        $t = TableSource::factory(['alias' => $expression]);
        $this->assertEquals(
            [null, $expression, 'alias'],
            [null, $t->getSource(), $t->getAlias()]
        );
    }
}
