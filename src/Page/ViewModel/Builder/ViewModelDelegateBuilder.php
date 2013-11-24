<?php
namespace Page\ViewModel\Builder;
use Page\Block\BlockInterface;
/**
 * Description of ViewModelDelegateBuilder
 *
 * @author tomoaki
 */
class ViewModelDelegateBuilder {
    use ServiceDependency;
    
    protected $overwrite;
    
    /**
     * 
     * @param bool $bool
     */
    public function setOverwrite($bool)
    {
        $this->overwrite = (bool) $bool;
    }
    
    public function associate(BlockInterface $block, BlockInterface $parent = null)
    {
        if (null !== $parent) {
            $block->setViewModel($parent->getViewModel());
        }
        return $block->getViewModel();;
    }
        
    public function build(BlockInterface $block)
    {
        $model = $block->getViewModel();
        
        return $model;
    }

    public function getContext() {
        
    }

    public function setContext($context) {
        
    }
}
