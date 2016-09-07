<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BuilderServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $builder = new Builder();

        if (!$container->has('Config')) {
            return $builder;
        }
        $config = $container->get('Config');
        if (!isset($config['sql_builder'])) {
            return $builder;
        }

        $config = $config['sql_builder'];

        if (isset($config['default_adapter'])) {
            $adapter = $container->get($config['default_adapter']);
            $builder->setDefaultAdapter($adapter);
        }

        if (isset($config['builders'])) {
            $builder->setPlatformBuilders($config['builders']);
        }

        return $builder;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, 'SqlBuilder');
    }
}
