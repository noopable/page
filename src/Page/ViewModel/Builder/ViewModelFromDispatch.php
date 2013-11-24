<?php
namespace Page\ViewModel\Builder;
use Zend\View\Model\ViewModel;
use Page\Block\BlockInterface;
use Page\Exception;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Description of ViewModelFromDispatch
 *
 * @author tomoaki
 */
trait ViewModelFromDispatch {
    use ServiceLocatorAwareTrait;
    
    protected $controller;
    
    protected $dispatchOptions;
    
    protected $signature;
    
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
    }
    
    public function setDispatchOptions($options)
    {
        $this->dispatchOptions = $options;
    }
    
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }
    
    protected function dispatch($name, array $params = null)
    {
        if (!isset($this->serviceLocator)) {
            throw new Exception\RuntimeException('Dispatch Process needs ServiceLocator');
        }
        
    }
    
    //put your code here

}
