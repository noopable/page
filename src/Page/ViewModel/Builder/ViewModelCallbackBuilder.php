<?php
namespace Page\ViewModel\Builder;
use Page\Exception;
use Page\Block\BlockInterface;
/**
 * Description of ViewModelCallbackBuilder
 *
 * @author tomoaki
 */
class ViewModelCallbackBuilder extends ViewModelBuilder {

    public function setCallback($callback)
    {
        if (! is_callable($callback)) {
            throw new Exception\RuntimeException('callback is not callable'); 
        }
        $this->callback = $callback;
    }
        
    public function build(BlockInterface $block)
    {
        $viewModel = parent::build($block);
        
        if (!isset($this->callback) || !is_callable($this->callback)) {
            return $viewModel;
        }
        
        call_user_func($this->callback, $block, $this->service);
        
        $viewModel = $block->getViewModel();
        return $viewModel;
    }
}
