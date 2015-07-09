<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Like;

class LikeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructEmptyArgs()
    {
        $like = new Like();
        $this->assertEquals('', $like->getIdentifier());
        $this->assertEquals('', $like->getLike());
    }

    public function testConstructWithArgs()
    {
        $like = new Like('bar', 'Foo%');
        $this->assertEquals('bar', $like->getIdentifier()->getValue());
        $this->assertEquals('Foo%', $like->getLike()->getValue());
    }

    public function testAccessorsMutators()
    {
        $like = new Like();
        $like->setIdentifier('bar');
        $this->assertEquals('bar', $like->getIdentifier()->getValue());
        $like->setLike('foo%');
        $this->assertEquals('foo%', $like->getLike()->getValue());
    }

    public function testInstanceOfPerSetters()
    {
        $like = new Like();
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Like', $like->setIdentifier('bar'));
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Like', $like->setLike('foo%'));
    }
}
