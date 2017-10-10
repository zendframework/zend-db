<?php

namespace ZendTest\Db\TestAsset;

use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\Select;

class WhereInvokable
{
    /** @var  string */
    private $value;

    /**
     * WhereInvokable constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke($select)
    {
        $select->where->addPredicate(new Like('foo', $this->value));
    }

}