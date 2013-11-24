<?php
namespace Page\Builder;

/**
 * Description of DelegateBlockBuilderTrait
 *
 * @author tomoaki
 */
trait DelegateBlockBuilderTrait {

    protected $delegateBuilder;
    
    public function setDelegateBuilder($delegateBuilder)
    {
        if (is_callable($delegateBuilder)) {
            $this->delegateBuilder = new BlockCallbackBuilder($this->service);
            $this->delegateBuilder->setCallback($callback);
        }
        elseif($delegateBuilder instanceof BlockBuilderInterface) {
            $this->delegateBuilder = $delegateBuilder;
        }
        elseif(is_string($delegateBuilder) && class_exists($delegateBuilder)) {
            $this->delegateBuilder = new $delegateBuilder($this->service);
        }
        elseif(is_array($delegateBuilder)) {
            if (isset($delegateBuilder['class'])) {
                $class = $delegateBuilder['class'];
                $options = @$delegateBuilder['options'] ?: array();
                $serviceLocator = $this->getServiceLocator();
                $di = $serviceLocator->get('Di');
                $options['service'] = $this->service;
                $options['serviceLocator'] = $serviceLocator;
                $this->delegateBuilder = $di->get($class, $options);
            }
        }
    }
    
    public function getDelegateBuilder()
    {
        return $this->delegateBuilder;
    }
    
}
