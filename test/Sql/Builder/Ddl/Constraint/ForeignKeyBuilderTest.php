<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Ddl\Constraint;

use Zend\Db\Sql\Builder\Builder;
use Zend\Db\Sql\Builder\sql92\Ddl\Constraint\ForeignKeyBuilder;
use Zend\Db\Sql\Ddl\Constraint\ForeignKey;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class ForeignKeyBuilderTest extends AbstractTestCase
{
    protected $builder;

    public function setUp()
    {
        $this->builder = new ForeignKeyBuilder(new Builder);
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testGetExpressionData()
    {
        $fk = new ForeignKey('foo', 'bar', 'baz', 'bam', 'CASCADE', 'SET NULL');
        $this->assertEquals(
            [[
                'CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s',
                [
                    new ExpressionParameter('foo',      ForeignKey::TYPE_IDENTIFIER),
                    new ExpressionParameter('bar',      ForeignKey::TYPE_IDENTIFIER),
                    new ExpressionParameter('baz',      ForeignKey::TYPE_IDENTIFIER),
                    new ExpressionParameter('bam',      ForeignKey::TYPE_IDENTIFIER),
                    new ExpressionParameter('CASCADE',  ForeignKey::TYPE_LITERAL),
                    new ExpressionParameter('SET NULL', ForeignKey::TYPE_LITERAL),
                ],
            ]],
            $this->builder->getExpressionData($fk, $this->context)
        );
    }
}
