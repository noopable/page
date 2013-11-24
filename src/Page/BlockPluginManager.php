<?php

namespace Page;

use Page\Block\BlockInterface;
use Zend\ServiceManager\AbstractPluginManager;

class BlockPluginManager extends AbstractPluginManager
{
    
    /**
     * Generic block
     *
     * @var array
     */
    protected $invokableClasses = array(
                'block'    => 'Page\Block\Block',
                'context'  => 'Page\Block\Context',
                'page'     => 'Page\Block\Page',
            );
    
    /**
     * 
     * @var bool Do not share instances
     */
    protected $shareByDefault = false;
    
    protected $config;
    
    protected $instances = array();

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  array $options
     * @param  bool $usePeeringServiceManagers
     * @return object
     */
    public function get($name, $options = array(), $usePeeringServiceManagers = true)
    {
        /**
         * 
         */
        if (isset($options['invokable'])) {
            if (is_array($options['invokable'])) {
                foreach($options['invokable'] as $key => $val) {
                    if (is_string($val)) {
                        $this->setInvokableClass($key, $val);
                    }
                }
            }
            elseif (is_string($options['invokable'])) {
                $this->setInvokableClass($name, $options['invokable']);
            }
        }
        
        if (!isset($options['name'])) {
            $options['name'] = $name;
        }
        
        if (isset($options['class'])) {
            $name = $options['class'];
        }
        
        // Allow specifying a class name directly; registers as an invokable class
        if (!$this->has($name) && $this->autoAddInvokableClass && class_exists($name)) {
            $this->setInvokableClass($name, $name);
        }
        

        
        if ($this->issetBlockInitializer()) {
            $this->getBlockInitializer()->setCreationOptions($options);
        }
        try {
            $instance = parent::get($name, $usePeeringServiceManagers);
        }
        catch (\Zend\ServiceManager\Exception\ServiceNotFoundException $e) {
            error_log('Warning : ' . $e->getMessage() . ' in ' . __FILE__ . ':' . __LINE__);
            $name = 'block';
            $instance = parent::get($name,  $usePeeringServiceManagers);
        }
        
        
        $this->validatePlugin($instance);
        return $instance;
    }
    
    public function setBlockInitializer(BlockInitializer $blockInitializer)
    {
        $this->addInitializer($blockInitializer, true);
        $this->blockInitializer = $blockInitializer;
    }
    
    public function getBlockInitializer()
    {
        return $this->blockInitializer;
    }
    
    public function issetBlockInitializer()
    {
        return isset($this->blockInitializer);
    }
    
    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof BlockInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\BlockInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
    
    /**
     * Attempt to create an instance via an invokable class
     *
     * Overrides parent implementation by invoking the route factory,
     * passing $creationOptions as the argument.
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return null|\stdClass
     * @throws Exception\RuntimeException If resolved class does not exist, or does not implement RouterInterface
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];
        if (!class_exists($invokable)) {
            throw new Exception\RuntimeException(sprintf(
                '%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist',
                __METHOD__,
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : ''),
                $canonicalName
            ));
        }

        if (!self::isSubclassOf($invokable, 'Page\Block\BlockInterface')) {
            throw new Exception\RuntimeException(sprintf(
                '%s: failed retrieving "%s%s" via invokable class "%s"; class does not implement %s\BlockInterface',
                __METHOD__,
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : ''),
                $canonicalName,
                __NAMESPACE__
            ));
        }

        $instance = new $invokable();

        return $instance;
    }
    
}
