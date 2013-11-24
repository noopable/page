<?php
namespace Page\Service;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Page\ProvidesConfigId;
/**
 * Description of ConfigFileResolverFactory
 *
 * @author tomoaki
 */
class ConfigFileResolverFactory implements FactoryInterface {
    use ProvidesConfigId;
    //put your code here
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $configResolver = null;
        $config = $serviceLocator->get('Config')[$this->getConfigId()];
        if (isset($config['config_resolver'])) {
            $resolveConf = $config['config_resolver'];
            $configResolver = new \Page\Config\ConfigFileResolver();
            if (isset($resolveConf['map'])) {
                $mapResolver =  new \Zend\View\Resolver\TemplateMapResolver($resolveConf['map']);
                $configResolver->attach($mapResolver);
            }
            
            if (isset($resolveConf['path_stack'])) {
                $stackResolver = new \Zend\View\Resolver\TemplatePathStack();
                $stackResolver->setDefaultSuffix('php');
                $stackResolver->addPaths($resolveConf['path_stack']);
                $configResolver->attach($stackResolver);
            }
            $service = $serviceLocator->get($config['service_name']);
            $service->setConfigResolver($configResolver);
        }
        return $configResolver;
    }
}
