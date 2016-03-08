<?php
namespace Page\Service;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Page\ProvidesConfigId;

/**
 * Description of ServiceFactory
 *
 * @author tomoaki
 */
class ServiceFactory  implements FactoryInterface {
    use ProvidesConfigId;
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config')[$this->getConfigId()];

        $service = new \Page\Service;

        $service->setServiceLocator($serviceLocator);

        $serviceConfig = new ServiceConfig($config);
        $serviceConfig->configure($service);

        return $service;
    }
}
