<?php
namespace Page;

use Page\BlockPluginManager as Blocks;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Description of ServiceSetterGetter
 *
 * @author tomoaki
 */
trait ServiceSetterGetter {
    protected $serviceLocator;

    protected $subscriber;
    
    protected $director;
    
    protected $resultPool;
    
    protected $blocks;
    
    protected $requestedName;
    
    protected $configResolver;
    
    protected $configLoader;
    
    protected $config;
    
    protected $errorPage;
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function setSubscriber(ListenerAggregateInterface $subscriber = null)
    {
        if (null === $subscriber) {
            $subscriber = new ServiceSubscriber($this);
        }
        $this->subscriber = $subscriber;
    }
    
    public function getSubscriber()
    {
        if (! isset($this->subscriber)) {
            $this->setSubscriber(null);
        }
        return $this->subscriber;
    }
    
    public function setViewModelBuildDirector(ViewModel\BuildDirector $viewModelBuildDirector = null)
    {
        if (null === $viewModelBuildDirector) {
            $viewModelBuildDirector = new ViewModel\BuildDirector($this);
        }
        $this->viewModelBuildDirector = $viewModelBuildDirector;
    }
    
    public function getViewModelBuildDirector()
    {
        if (! isset($this->viewModelBuildDirector)) {
            $this->setViewModelBuildDirector(null);
        }
        return $this->viewModelBuildDirector;
    }
    
    /**
     * 
     * @param \Page\Builder\BuilderFactory $builderFactory
     */
    public function setBuilderFactory(Builder\BuilderFactory $builderFactory = null)
    {
        if (null === $builderFactory) {
            $builderFactory = new Builder\BuilderFactory($this);
            $builderFactory->setServiceLocator($this->getServiceLocator());
        }
        $this->builderFactory = $builderFactory;
    }
    
    /**
     * 
     * @return Builder\BuilderFactory
     */
    public function getBuilderFactory()
    {
        if (!isset($this->builderFactory)) {
            $this->setBuilderFactory(null);
        }
        return $this->builderFactory;
    }
    
    public function setResultPool(ResultPool $resultPool = null)
    {   
        if (null === $resultPool) {
            $resultPool = new ResultPool;
        }
        $this->resultPool = $resultPool;
    }
    
    /**
     * 
     * @return ResultPool
     */
    public function getResultPool()
    {
        if (!isset($this->resultPool)) {
            $this->setResultPool(null);
        }
        return $this->resultPool;
    }
    
    public function getBlocks()
    {
        if (! isset($this->blocks)) {
            $this->setBlocks(null);
        }
        return $this->blocks;
    }
    
    public function setBlocks(Blocks $blocks = null)
    {
        if (null === $blocks) {
            $blocks = new BlockPluginManager();
            $initializer = new BlockInitializer($this);
            $blocks->setBlockInitializer($initializer);
        }
        $this->blocks = $blocks;
        return $this;
    }
    
    public function setRequestedName($name)
    {
        $this->requestedName = $name;
    }
    
    public function getRequestedName()
    {
        return $this->requestedName;
    }
    
    public function setConfigLoader(Config\Loader\ConfigLoaderInterface $configLoader)
    {
        $this->configLoader = $configLoader;
    }
    
    public function getConfigLoader()
    {
        if (!isset($this->configLoader)) {
            throw new Exception\RuntimeException('Config Loader is not set');
        }
        return $this->configLoader;
    }
    
    public function setConfigResolver(Config\ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }
    
    public function getConfigResolver()
    {
        if (!isset($this->configResolver)) {
            throw new Exception\RuntimeException('Config Resolver is not set');
        }
        
        return $this->configResolver;
    }

    /**
     * 
     * @param string $page
     */
    public function setErrorPage($errorPage)
    {
        $this->errorPage = $errorPage;
    }
    
    /**
     * 
     * @return string
     */
    public function getErrorPage()
    {
        return $this->errorPage;
    }
}
