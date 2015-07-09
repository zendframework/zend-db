<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Predicate;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\ExpressionParameter;

class InTest extends TestCase
{
    public function testEmptyConstructorYieldsNullIdentifierAndValueSet()
    {
        $in = new In();
        $this->assertNull($in->getIdentifier());
        $this->assertNull($in->getValueSet());
    }

    public function testCanPassIdentifierAndValueSetToConstructor()
    {
        $in = new In('foo.bar', [1, 2]);
        $this->assertEquals('foo.bar', $in->getIdentifier()->getValue());
        $this->assertEquals(
            [
                new ExpressionParameter(1),
                new ExpressionParameter(2),
            ],
            $in->getValueSet()
        );
    }

    public function testIdentifierIsMutable()
    {
        $in = new In();
        $in->setIdentifier('foo.bar');
        $this->assertEquals('foo.bar', $in->getIdentifier()->getValue());
    }

    public function testValueSetIsMutable()
    {
        $in = new In();
        $in->setValueSet([1, 2]);
        $this->assertEquals(
            [
                new ExpressionParameter(1),
                new ExpressionParameter(2),
            ],
            $in->getValueSet()
        );
    }
}
