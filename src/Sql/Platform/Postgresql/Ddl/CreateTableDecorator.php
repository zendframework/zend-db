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
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\Index\Index;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\Postgresql\Ddl\Index\IndexDecorator;

class CreateTableDecorator extends CreateTable implements PlatformDecoratorInterface
{
    const INDEXES = 'indexes';

    /**
     * @var CreateTable
     */
    protected $subject;

    /**
     * @var IndexDecorator[]
     */
    protected $indexes = [];

    /**
     * @inheritDoc
     */
    protected $indexSpecification = [
        self::INDEXES => [
            "\n%1\$s" => [
                [1 => '%1$s;', 'combinedby' => "\n"]
            ]
        ]
    ];

    /**
     * @param $subject
     * @return mixed
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        $this->specifications = array_merge($this->specifications, $this->indexSpecification);
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

        return parent::buildSqlString($platform, $driver, $parameterContainer);
    }

    private function separateIndexesFromConstraints()
    {
        // take advantage of PHP's ability to access protected properties of different instances created from same class
        $this->indexes = array_filter($this->subject->constraints, function ($constraint) {
            return $constraint instanceof Index;
        });

        $filteredConstraints = array_filter($this->subject->constraints, function ($constraint) {
            return !($constraint instanceof Index);
        });

        $this->subject->constraints = $filteredConstraints;

        array_walk($this->indexes, function (&$index, $key) {
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
    protected function processIndexes(PlatformInterface $adapterPlatform = null)
    {
        if (!$this->indexes) {
            return;
        }

        $sqls = [];

        foreach ($this->indexes as $index) {
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
        return ["\n);"];
    }
}