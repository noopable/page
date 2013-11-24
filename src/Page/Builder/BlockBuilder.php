<?php
namespace Page\Builder;
use Page\ServiceDependency;
use Page\BlockPluginManager;
use Page\Block\BlockInterface;
/**
 * Description of BlockBuilder
 *
 * @author tomoaki
 */
class BlockBuilder implements BlockBuilderInterface {
    use ServiceDependency;
    
    protected $block;
    
    public function build(BlockInterface $block){
        $this->block = $block;
        return $block;
    }
    
    //public function getBlock($name, $config = array(), $loadConfig = true)
    
    public function load($name)
    {
        $this->service->loadBlockConfig($name);
    }
    
    /**
     * 
     * @param type $name
     * @param type $config
     * @return type
     */
    public function get($name, $config = array())
    {
        return $this->service->getBlock($name, $config);
    }
    
    public function simpleGet($name)
    {
        
    }
    
    public function setInvokable($name, $class)
    {
        $this->service->getBlocks()->setInvokable($name, $class);
        return $this;
    }
            
    public function block($name, $config = array(), $load = true)
    {
        $config['class'] = 'block';
        $block = $this->service->getBlock($name, $config, $load);
        if (isset($this->block)) {
            $this->block->insertBlock($block);
        }
        return $this;
    }
    
    public function insert()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_string($arg)) {
                $name = $arg;
            }
            
            if (is_array($arg)) {
                $config = $arg;
            }
            
            if (is_bool($arg)) {
                $load = $arg;
            }
            
            if (is_object($arg)
                && $arg instanceof \Page\Block\BlockInterface) {
                $this->block->insertBlock($arg);
            }
        }
        
        if (!isset($config)) {
            $config = array();
        }
        
        if (!isset($name)) {
            if (isset($config['name'])) {
                $name = $config['name'];
            }
            else {
                $name = uniqid('block_');
                if (! isset($config['class'])) {
                    $config['class'] = 'block';
                }
            }
        }
        
        if (!isset($load)) {
            $load = true;
        }
        
        $block = $this->service->getBlock($name, $config, $load);
        if (isset($this->block)) {
            $this->block->insertBlock($block);
        }
        
        return $this;
    }
    
    public function __invoke() {
        return call_user_func_array(array($this, 'insert'), func_get_args());
    }
}
