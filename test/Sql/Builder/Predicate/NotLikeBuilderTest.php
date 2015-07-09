<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\NotLikeBuilder;
use Zend\Db\Sql\Predicate\NotLike;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class NotLikeBuilderTest extends AbstractTestCase
{
    protected $expression;
    protected $builder;

    public function setUp()
    {
        $this->builder = new NotLikeBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testGetExpressionData()
    {
        $notLike = new NotLike('bar', 'Foo%');
        $this->assertEquals(
            [[
                '%1$s NOT LIKE %2$s',
                [
                    new ExpressionParameter('bar',  $notLike::TYPE_IDENTIFIER),
                    new ExpressionParameter('Foo%', $notLike::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($notLike, $this->context)
        );
    }
}
