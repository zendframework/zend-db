<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Postgresql\Ddl;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Index\Index;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Platform\Postgresql\Ddl\Index\IndexDecorator;

class AlterTableDecorator extends AlterTable implements PlatformDecoratorInterface
{
    const ADD_INDEXES = 'addIndexes';

    /**
     * @var AlterTable
     */
    protected $subject;

    /**
     * @var IndexDecorator[]
     */
    protected $addIndexes = [];

    /**
     * @var bool
     */
    private $hasCreateTable = true;

    protected $indexSpecification = [
        'statementEnd' => '%1$s',
        self::ADD_INDEXES => [
            "%1\$s" => [
                [1 => '%1$s;', 'combinedby' => "\n"]
            ]
        ],
    ];

    /**
     * @inheritDoc
     */
    public function setSubject($subject) {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function buildSqlString(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        $this->separateIndexesFromConstraints();
        $this->deleteUnneededSpecification();

        $alterTable = '';
        if ($this->hasCreateTable) {
            $alterTable = parent::buildSqlString($platform, $driver, $parameterContainer);
            $this->subject->specifications = $this->specifications = $this->indexSpecification;
        }

        $indexes = parent::buildSqlString($platform, $driver, $parameterContainer);
        return $alterTable.$indexes;
    }

    private function separateIndexesFromConstraints()
    {
        // take advantage of PHP's ability to access protected properties of different instances created from same class
        $this->addIndexes = array_filter($this->subject->addConstraints, function($constraint) {
            return $constraint instanceof Index;
        });

        $filteredConstraints = array_filter($this->subject->addConstraints, function($constraint) {
            return !($constraint instanceof Index);
        });

        $this->subject->addConstraints = $filteredConstraints;

        array_walk($this->addIndexes, function (&$index, $key) {
            $indexDecorator = new IndexDecorator();
            $indexDecorator->setSubject($index);
            $indexDecorator->setTable($this->subject->table);
            $index = $indexDecorator;
        });
    }

    /**
     * @param PlatformInterface|null $adapterPlatform
     * @return array|void
     */
    protected function processAddIndexes(PlatformInterface $adapterPlatform = null) {
        if (!$this->addIndexes) {
            return;
        }

        $sqls = [];

        foreach ($this->addIndexes as $index) {
            $sqls[] = $this->processExpression($index, $adapterPlatform);
        }

        return [$sqls];
    }

    /**
     * @param PlatformInterface|null $adapterPlatform
     * @return array|void
     */
    protected function processStatementEnd(PlatformInterface $adapterPlatform = null)
    {
        return [";\n"];
    }

    private function deleteUnneededSpecification()
    {
        $subject = $this->subject;
        if (!($subject->addColumns || $subject->changeColumns || $subject->dropColumns
            || $subject->addConstraints || $subject->dropConstraints)) {

            $this->hasCreateTable = false;

            unset($this->indexSpecification['statementEnd']);
            $this->subject->specifications = $this->specifications = $this->indexSpecification;
        }
    }
}
