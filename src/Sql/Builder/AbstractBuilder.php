<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\PreparableSqlObjectInterface;
use Zend\Db\Sql\SqlObjectInterface;

class AbstractBuilder implements PlatformDecoratorInterface, PreparableSqlObjectInterface, SqlObjectInterface
{
    /**
     * @var object|null
     */
    protected $subject;

    /**
     * @var PlatformDecoratorInterface[]
     */
    protected $decorators = [];

    /**
     * {@inheritDoc}
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string                     $type
     * @param PlatformDecoratorInterface $decorator
     *
     * @return void
     */
    public function setTypeDecorator($type, PlatformDecoratorInterface $decorator)
    {
        $this->decorators[$type] = $decorator;
    }

    /**
     * @param PreparableSqlObjectInterface|SqlObjectInterface $subject
     * @return PlatformDecoratorInterface|PreparableSqlObjectInterface|SqlObjectInterface
     */
    public function getTypeDecorator($subject)
    {
        foreach ($this->decorators as $type => $decorator) {
            if ($subject instanceof $type) {
                $decorator->setSubject($subject);

                return $decorator;
            }
        }

        return $subject;
    }

    /**
     * @return array|PlatformDecoratorInterface[]
     */
    public function getDecorators()
    {
        return $this->decorators;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        if (! $this->subject instanceof PreparableSqlObjectInterface) {
            throw new Exception\RuntimeException('The subject does not appear to implement Zend\Db\Sql\PreparableSqlObjectInterface, thus calling prepareStatement() has no effect');
        }

        $this->getTypeDecorator($this->subject)->prepareStatement($adapter, $statementContainer);

        return $statementContainer;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null)
    {
        if (! $this->subject instanceof SqlObjectInterface) {
            throw new Exception\RuntimeException('The subject does not appear to implement Zend\Db\Sql\SqlObjectInterface, thus calling prepareStatement() has no effect');
        }

        return $this->getTypeDecorator($this->subject)->getSqlString($adapterPlatform);
    }
}
