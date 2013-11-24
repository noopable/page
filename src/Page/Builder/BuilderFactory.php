<?php
namespace Page\Builder;

use Page\Block\BlockInterface;
use Page\ServiceDependency;

use Zend\Di\Di;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class BuilderFactory implements ServiceLocatorAwareInterface 
{
    use ServiceLocatorAwareTrait;
    use ServiceDependency;
    
    public function setDi(Di $di)
    {
        $this->di = di;
    }
    
    public function getDi()
    {
        if (!isset($this->di) && isset($this->serviceLocator)) {
            if ($this->serviceLocator->has('Di')) {
                $this->di = $this->serviceLocator->get('Di');
            }
        }
        return $this->di;
    }
    
    public function getBlockBuilderFromBlock(BlockInterface $block)
    {
        /**
         *
         */
        $builder = $block->getBlockBuilder();
        
        if (null === $builder) {
            /**
             * 
             */
            $builder = $block->getOption('blockBuilder', null);
            if (null === $builder) {
                return null;
            }
        }
        
        if ($builder instanceof BlockBuilderInterface) {
            return $builder;
        }
        
        if (is_callable($builder)) {
            $def = array('callback' => $builder);
        }
        
        if (is_string($builder) && class_exists($builder)) {
            $def['class'] = $builder;
            $def['options'] = array();
        }
        
        /**
         * 
         */
        if (is_array($builder)) {
            $def = $builder;
        }
        
        if (isset($def)) {
            return $this->getBlockBuilder($def);
        }
        
        return null;
    }
    
    public function getBlockBuilder(array $def)
    {
        if (isset($def['callable']) && is_callable($def['callable'])) {
            $callback = $def['callable'];
        }
        elseif (isset($def['callback']) && is_callable($def['callback'])) {
            $callback = $def['callback'];
        }
        
        if (isset($callback)) {
            $builder = new BlockCallbackBuilder($this->service);
            $builder->setCallback($callback);
            return $builder;
        }
        
        /**
         * 設定によるビルダーの取得
         * 
         */
        if (isset($def['class'])) {
            $class = $def['class'];
            $options = @$def['options'] ?: array();
            $serviceLocator = $this->getServiceLocator();
            $di = $serviceLocator->get('Di');
            if (!isset($di)) {
                trigger_error('Di is not set', E_USER_WARNING);
                return null;
            }
            $options['service'] = $this->service;
            $options['serviceLocator'] = $serviceLocator;
            $builder = $di->get($class, $options);
            
            return $builder;
        }
    }
}
