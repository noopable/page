<?php
namespace Page\Builder;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

use Page\Block\BlockInterface;

/**
 * Description of BlockAggregateBuilder
 *
 * @author tomoaki
 */
class BlockRelayBuilder extends BlockBuilder implements ServiceLocatorAwareInterface {
    use ServiceLocatorAwareTrait;
    use DelegateBlockBuilderTrait;
    
    /**
     * 
     * @param \Page\Block\BlockInterface $block
     * @return type
     */
    public function build(BlockInterface $block){
        if ($parent = $block->getParent()) {
            $this->block = $parent;
        }
        else {
            $this->block = $block;
        }
        
        if ($delegateBuilder = $this->getDelegateBuilder()) {
            $delegateBuilder->build($this->block);
        }
        
        return $this->block;
    }
    
}
