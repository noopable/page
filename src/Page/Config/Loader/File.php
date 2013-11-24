<?php
namespace Page\Config\Loader;
use Page\Config\ConfigResolverInterface;
/**
 * Description of ConfigFileLoader
 *
 * @author tomoaki
 */
class File implements ConfigLoaderInterface {
    
    protected $resolver;
    
    protected $config;
    
    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            $this->config = $config;
        }
    }
    
    public function load($name) {
        if (isset($this->resolver)) {
            $__file__ = $this->resolver->resolve($name);
            unset($name);
            if (is_file($__file__)) {
                ob_start();
                $config = include $__file__;
                ob_end_clean();
            }
        }
        if (!isset($config)) {
            $config = array();
        }
        return $config;
    }
    
    /**
     * 
     * @param \Page\Config\ConfigResolverInterface $resolver
     */
    public function setResolver(ConfigResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }
    
    public function getResolver()
    {
        return $this->resolver;
    }
    
    public function hasResolver()
    {
        return isset($this->resolver);
    }
    
}
