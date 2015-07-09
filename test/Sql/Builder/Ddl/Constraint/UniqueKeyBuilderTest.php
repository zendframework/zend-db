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
use Zend\Db\Sql\Builder\sql92\Ddl\Constraint\UniqueKeyBuilder;
use Zend\Db\Sql\Ddl\Constraint\UniqueKey;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class UniqueKeyBuilderTest extends AbstractTestCase
{
    protected $builder;

    public function setUp()
    {
        $this->builder = new UniqueKeyBuilder(new Builder);
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testGetExpressionData()
    {
        $uk = new UniqueKey('foo', 'my_uk');
        $this->assertEquals(
            [[
                'CONSTRAINT %s UNIQUE (%s)',
                [
                    new ExpressionParameter('my_uk', $uk::TYPE_IDENTIFIER),
                    new ExpressionParameter('foo',   $uk::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($uk, $this->context)
        );
    }
}
