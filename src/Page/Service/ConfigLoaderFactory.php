<?php
namespace Page\Service;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Page\ProvidesConfigId;
use Page\Exception;
/**
 * Description of ConfigLoaderFactory
 *
 * @author tomoaki
 */
class ConfigLoaderFactory implements FactoryInterface {
    use ProvidesConfigId;
    
    protected $defaultClass = 'Page\Config\Loader\File';
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
                
        if (isset($config['config_loader'])) {
            $loaderConfig = $config['config_loader'];
        }

        if (is_string($loaderConfig)
                && class_exists($loaderConfig)) {
            $class = $loaderConfig;
        }
        
        if (is_array($loaderConfig)) {
            
            if (isset($loaderConfig['class'])) {
                $class = $loaderConfig['class'];
            }
            
            if (isset($loaderConfig['def'])) {
                $def = $loaderConfig['def'];
            }
            
        }
        
        if (! isset($class)) {
            $class = $this->defaultClass;
        }
        
        /**
         * configを直接投入することが前提であれば、
         * 依存性をDiで解決するメリットがない。
         * 
         */
        if ($serviceLocator->has('Di')) {
            $di = $serviceLocator->get('Di');
            if (isset($def)) {
                $configLoader = $di->get($class, array('config' => $def));
            }
            else {
                $configLoader = $di->get($class);
            }
        }
        else {
            $configLoader = new $class;
        }
        
        if (method_exists($configLoader, 'setResolver')
            && method_exists($configLoader, 'hasResolver')
            && !$configLoader->hasResolver()) {
            $resolver = $serviceLocator->get($config['config_resolver_name']);
            $configLoader->setResolver($resolver);
        }
        
        if (! $configLoader instanceof \Page\Config\Loader\ConfigLoaderInterface) {
            throw new Exception\RuntimeException('configLoader needs to implement ConfigLoaderInterface');
        }
        
        return $configLoader;
    }
}
