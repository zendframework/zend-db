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
use Zend\Db\Sql\Builder\sql92\Ddl\Constraint\CheckBuilder;
use Zend\Db\Sql\Ddl\Constraint\Check;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class CheckBuilderTest extends AbstractTestCase
{
    protected $context;

    protected $builder;

    public function setUp()
    {
        $this->builder = new CheckBuilder(new Builder);
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testGetExpressionData()
    {
        $check = new Check('id>0', 'foo');
        $this->assertEquals(
            [[
                'CONSTRAINT %s CHECK (%s)',
                [
                    new ExpressionParameter('foo',  $check::TYPE_IDENTIFIER),
                    new ExpressionParameter('id>0', $check::TYPE_LITERAL),
                ],
            ]],
            $this->builder->getExpressionData($check, $this->context)
        );
    }
}
