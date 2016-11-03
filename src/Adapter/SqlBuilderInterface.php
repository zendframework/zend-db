<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Adapter;

interface SqlBuilderInterface
{
    /**
     * @param mixed $object
     * @param null|AdapterInterface $adapter
     * @return string
     */
    public function buildSqlString($object, AdapterInterface $adapter = null);

    /**
     * @param mixed $object
     * @param null|AdapterInterface $adapter
     * @return Driver\StatementInterface
     */
    public function prepareSqlStatement($object, AdapterInterface $adapter = null);
}
