<?php
namespace Page\Builder;
use Page\Block\BlockInterface;
/**
 * Description of BlockCallbackBuilder
 *
 * @author tomoaki
 */
class BlockCallbackBuilder extends BlockBuilder {
    
    protected $callback;
    
    protected $block;
    
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }
    
    public function build(BlockInterface $block)
    {
        if (!isset($this->callback) || !is_callable($this->callback)) {
            return $block;
        }
        
        $this->block = $block;
        $callback = $this->callback;
        $res = $callback($this);
        if ($res instanceof BlockInterface) {
            return $res;
        }
        
        return $block;
    }
    
    public function getService()
    {
        return $this->service;
    }
    
    

}
