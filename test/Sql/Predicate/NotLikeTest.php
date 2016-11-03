<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\NotLike;

class NotLikeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructEmptyArgs()
    {
        $notLike = new NotLike();
        $this->assertEquals('', $notLike->getIdentifier());
        $this->assertEquals('', $notLike->getLike());
    }

    public function testConstructWithArgs()
    {
        $notLike = new NotLike('bar', 'Foo%');
        $this->assertEquals('bar', $notLike->getIdentifier()->getValue());
        $this->assertEquals('Foo%', $notLike->getLike()->getValue());
    }

    public function testAccessorsMutators()
    {
        $notLike = new NotLike();
        $notLike->setIdentifier('bar');
        $this->assertEquals('bar', $notLike->getIdentifier()->getValue());
        $notLike->setLike('foo%');
        $this->assertEquals('foo%', $notLike->getLike()->getValue());
    }

    public function testInstanceOfPerSetters()
    {
        $notLike = new NotLike();
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Like', $notLike->setIdentifier('bar'));
        $this->assertInstanceOf('Zend\Db\Sql\Predicate\Like', $notLike->setLike('foo%'));
    }
}
