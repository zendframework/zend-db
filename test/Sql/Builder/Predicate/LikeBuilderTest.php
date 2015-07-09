<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\LikeBuilder;
use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\Builder\Context;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class LikeBuilderTest extends AbstractTestCase
{
    protected $builder;

    public function setUp()
    {
        $this->builder = new LikeBuilder(new \Zend\Db\Sql\Builder\Builder());
        $this->context = new Context($this->getAdapterForPlatform('sql92'));
    }

    public function testGetExpressionData()
    {
        $like = new Like('bar', 'Foo%');
        $this->assertEquals(
            [[
                '%1$s LIKE %2$s',
                [
                    new ExpressionParameter('bar',  $like::TYPE_IDENTIFIER),
                    new ExpressionParameter('Foo%', $like::TYPE_VALUE),
                ],
            ]],
            $this->builder->getExpressionData($like, $this->context)
        );

        $like = new Like(['Foo%'=>$like::TYPE_VALUE], ['bar'=>$like::TYPE_IDENTIFIER]);
        $this->assertEquals(
            [[
                '%1$s LIKE %2$s',
                [
                    new ExpressionParameter('Foo%', $like::TYPE_VALUE),
                    new ExpressionParameter('bar',  $like::TYPE_IDENTIFIER),
                ],
            ]],
            $this->builder->getExpressionData($like, $this->context)
        );
    }
}
