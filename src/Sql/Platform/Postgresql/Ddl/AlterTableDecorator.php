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
    const DROP_INDEXES = 'dropIndexes';

    /**
     * @var AlterTable
     */
    protected $subject;

    /**
     * @var IndexDecorator[]
     */
    protected $addIndexes = [];

    /**
     * @var string[]
     */
    protected $dropIndexes = [];

    /**
     * @var bool
     */
    private $hasCreateTable = true;

    /**
     * Compensate for dropConstraint() interface not distinguishing between string and index object.
     * Add IF EXISTS for safety to handle either until/if new signature is approved.
     *
     * @var array
     */
    protected $dropConstraintSpecification = [
        "%1\$s" => [
            [1 => "DROP CONSTRAINT IF EXISTS %1\$s,\n", 'combinedby' => ""],
        ]
    ];

    protected $indexSpecification = [
        'statementEnd' => '%1$s',
        self::ADD_INDEXES => [
            "%1\$s" => [
                [1 => '%1$s;', 'combinedby' => "\n"]
            ]
        ],
        self::DROP_INDEXES => [
            "%1\$s" => [
                [1 => 'DROP INDEX IF EXISTS %1$s;', 'combinedby' => "\n"]
            ]
        ]
    ];

    /**
     * @inheritDoc
     */
    public function setSubject($subject) {
        $this->subject = $subject;

        $this->specifications[self::DROP_CONSTRAINTS] = $this->dropConstraintSpecification;
        $this->subject->specifications = $this->specifications;

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
        $this->duplicateDropConstraintToDropIndex();
        $this->deleteUnneededSpecification();

        // unlike CreateTableDecorator where CREATE TABLE is always present for new tables, regardless of Incex creation
        // PostgreSQL does not use ALTER TABLE to add/drop indexes to existing tables.
        // Therefore, if the only change is index related, DDL would have dangling ALTER TABLE.
        // Consequently, table alterations outside of ALTER TABLE syntax get processed as whole different specification chunk
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
     * DROP CONSTRAINT always with DROP INDEX to compensate for dropConstraint() interface
     * only accepting strings, not inspectable objects.
     * @TODO if new signature removeConstraint(string|AbstractConstraint) gets approved, delete this method
     */
    private function duplicateDropConstraintToDropIndex()
    {
        $this->dropIndexes = $this->subject->dropConstraints;
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

    protected function processDropIndexes(PlatformInterface $adapterPlatform = null) {
        if (!$this->dropIndexes) {
            return;
        }

        $sqls = [];

        foreach ($this->dropIndexes as $index) {
            $sqls[] = $adapterPlatform->quoteIdentifier($index);
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
