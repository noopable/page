<?php
namespace Page\ViewModel\Builder;
use Page\Block\BlockInterface;
/**
 *
 * @author tomoaki
 */
interface ViewModelBuilderInterface {

    public function build(BlockInterface $block);
    
    public function associate(BlockInterface $block, BlockInterface $parent = null);
    
    public function setContext($context);
    
    public function getContext();
}
