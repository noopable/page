<?php
namespace Page\ViewModel\Builder;
use Zend\View\Model\ViewModel;

use Page\Block\BlockInterface;
use Page\Exception;

/**
 * Description of ViewModelDispatchBuilder
 *
 * @author tomoaki
 */
class ViewModelDispatchBuilder extends ViewModelBuilder {
    
    use \Flower\DispatcherTrait;
    
    public function build(BlockInterface $block)
    {
        if (!isset($this->serviceLocator)) {
            throw new Exception\RuntimeException('Missing ServiceLocator ::(' . $block->getName() . ')');
        }
        
        if (!isset($this->controllerName)) {
            throw new Exception\RuntimeException('Missing ControllerName ::(' . $block->getName() . ')');
        }
        
        if (isset($this->signature)) {
            $filter = array_intersect_key($this->dispatchOptions, array_flip((array) $this->signature));
        }
        else {
            $filter = $this->dispatchOptions;
        }

        $resultPool = $this->service->getResultPool();
        $viewModel = $resultPool->getResultViewModel($this->controllerName, $filter);
        if (! $viewModel){
            if (! isset($this->dispatchOptions)) {
                $this->dispatchOptions = array('action' => 'index');
            }
            try {
                $viewModel = $this->dispatch();
            }
            catch (\Zend\ServiceManager\Exception\ServiceNotFoundException $e) {
                trigger_error('Ignore \'ServiceNotFoundException\' :: \'' . $e->getMessage() . '\' in the block(' . $block->getName() . ')', E_USER_WARNING);
                trigger_error($e->getTraceAsString() . ' PHP Notice: EOL' , E_USER_NOTICE);
            }
        }

        if ($viewModel instanceof ViewModel) {
            $block->setViewModel($viewModel);
        }
            
        return parent::build($block);
    }
     
}
