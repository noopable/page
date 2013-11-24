<?php
namespace Page\ViewModel\Builder;
use Page\Block\BlockInterface;
use Page\ServiceDependency;
/**
 * Description of ViewModelBuilder
 *
 * @author tomoaki
 */
class ViewModelBuilder implements ViewModelBuilderInterface{
    use ServiceDependency;
    public function associate(BlockInterface $block, BlockInterface $parent = null)
    {
        $model = $block->getViewModel();
        if (null !== $parent) {
            $parent->getViewModel()->addChild($model);
        }
            
        return $model;
    }
    
    public function build(BlockInterface $block)
    {
        $model = $block->getViewModel();
        
        $model->setTemplate($this->service->getTemplate($block));

        if ($captureTo = $block->getOption('captureTo', false)) {
            $model->setCaptureTo($captureTo);
        }

        if ($append = $block->getOption('viewModelAppend', null)) {
            $model->setAppend($append);
        }

        $model->setVariables($block->getProperties(), false);

        if(!$name = $model->getVariable('name', false)) {
            $model->setVariable('name', $block->getName());
        }

        return $model;
    }

    public function getContext() {
        
    }

    public function setContext($context) {
        
    }
}
