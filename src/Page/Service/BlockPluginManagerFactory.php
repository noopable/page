<?php
namespace Page\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Config as ServiceManagerConfig;
use Zend\ServiceManager\Di\DiAbstractServiceFactory;
use Zend\ServiceManager\Di\DiServiceInitializer;

use Page\ProvidesConfigId;

/**
 * Description of BlockPluginManagerFactory
 *
 * @author tomoaki
 */
class BlockPluginManagerFactory implements FactoryInterface {
    use ProvidesConfigId;
    /**
     * Create and return abstract factory seeded by dependency injector
     *
     * Creates and returns an abstract factory seeded by the dependency
     * injector. If the "di" key of the configuration service is set, that
     * sub-array is passed to a DiConfig object and used to configure
     * the DI instance. The DI instance is then used to seed the
     * DiAbstractServiceFactory, which is then registered with the service
     * manager.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Di
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config')[$this->getConfigId()];
        $service =  $serviceLocator->get($config['service_name']);
        
        if (isset($config['block_plugin'])) {
            $blockPlugin = $config['block_plugin'];
        }
        else {
            $blockPlugin = array();
        }
            
        if (is_array($blockPlugin)) {
            $managerConfig = new ServiceManagerConfig($blockPlugin);
            $blockPlugin = new \Page\BlockPluginManager($managerConfig);
        }

        if($blockPlugin instanceof \Page\BlockPluginManager) {
            $initializer = new \Page\BlockInitializer($service);
            $initializer->setServiceLocator($serviceLocator);
            $blockPlugin->setBlockInitializer($initializer);
        }
        
        if ($serviceLocator->has('Di')) {
            $di = $serviceLocator->get('Di');
            $blockPlugin->addAbstractFactory(
                //new DiAbstractServiceFactory($di, DiAbstractServiceFactory::USE_SL_BEFORE_DI)
                new DiAbstractServiceFactory($di, DiAbstractServiceFactory::USE_SL_NONE)
            );
            $blockPlugin->addInitializer(
                new DiServiceInitializer($di, $serviceLocator)
            );
        }
        return $blockPlugin;
    }
}
