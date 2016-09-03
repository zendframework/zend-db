<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Postgresql\Ddl\Index;

use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Db\Sql\Ddl\Index\Index;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class IndexDecorator extends Index implements PlatformDecoratorInterface
{

    /**
     * @var Index
     */
    protected $subject = null;

    protected $specification = 'CREATE INDEX %s ON %s(...)';

    private $table;

    /**
     * @inheritDoc
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->subject->specification = $this->specification;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function getExpressionData()
    {
        if (!$this->table) {
            throw new InvalidQueryException('PostgreSQL Index needs table name specified.');
        }

        $expressionData = $this->subject->getExpressionData();

        // [0] => specification
        // [1] => values
        // [2] => types
        array_splice($expressionData[0][1], 1, 0, $this->table);
        array_splice($expressionData[0][2], 1, 0, self::TYPE_IDENTIFIER);

        return $expressionData;
    }
}
