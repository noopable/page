<?php
namespace Page\ViewModel;

use Page\Service;
use Page\Block\BlockInterface;

use Zend\View\Model\ViewModel;
use Zend\Stdlib\ArrayUtils;

use RecursiveCallbackFilterIterator;
use RecursiveIteratorIterator;
/**
 * Description of ViewModelBuildDirector
 *
 * @author tomoaki
 */
class BuildDirector {

    protected $service;
    
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    
    /**
     * 
     * @param \Page\Block\BlockInterface $block
     * @return void
     */
    public function build(BlockInterface $block = null)
    {
        if (null === $block) {
            $isPageBlock = true;
            $block = $this->service->getPage();
            if (! $block->getTemplate()) {
                $layout = $this->service->getServiceLocator()
                    ->get('ViewManager')->getLayoutTemplate();
                $block->setTemplate($layout);

            }
        }
        $viewModel = $this->buildViewModel($block);
            
        if (isset($isPageBlock) && $isPageBlock) {
            // use it as the terminal ViewModel (is the layout)
            $viewModel->setTerminal(true);
        }
                
        return $viewModel;
    }        
    
    /**
     * 
     * @param BlockInterface $block
     * @param bool $recursive
     * @return \Zend\View\Model\ViewModel
     */
    public function buildViewModel(BlockInterface $block, $recursive = true)
    {
        $director = $this;
        $callback = function ($current, $key, $iterator) use ($recursive, $director) {
            $state = $current->getState();
            
            if ($state->checkFlag($state::PREPARE_VIEW_MODEL)) {
                return $recursive;
            }
            $builder = $director->getViewModelBuilder($current);
            $builder->build($current);
            if (method_exists($iterator, 'getParent')) {
                $builder->associate($current, $iterator->getParent());
            }
            $viewModel = $current->getViewModel();
            
            $state->setFlag($state::PREPARE_VIEW_MODEL);
            if (! $template = $viewModel->getTemplate()) {
                trigger_error("template not found in " . $current->getName(), E_USER_WARNING);
            }
            return $recursive;
        };
        $iterator = new RecursiveCallbackFilterIterator($block, $callback);
        $rii = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        $rii->rewind();
        while($rii->valid()) {
            $rii->next();
        };
        return $block->getViewModel();
    }
    
    public function setContext(Context $context)
    {
        $this->context = $context;
        return $this;
    }
    
    public function getContext()
    {
        return $this->context;
    }
    
    public function getViewModelBuilder(BlockInterface $block)
    {
        if ($builder = $block->getViewModelBuilder()) {
            if ($builder instanceof ViewModelBuilderInterface) {
                return $builder;
            }
            elseif (is_array($builder)) {
                $def = $builder;
                unset($builder);
            }
        }
        else {
            $builder =  $block->getOption('viewModelBuilder', array());
            if (is_array($builder)) {
                $def = $builder;
                unset($builder);
            }
        }
        
        if (isset($builder) && is_callable($builder)) {
            $def['policy'] = 'callback';
            $def['option']['callback'] = $builder;
            if (isset($def['callback'])) {
                $def['option']['callback'] = $def['callback'];
            }
        }
        
        if (isset($def['policy'])) {
            $policy = $def['policy'];
        }
        else {
            $policy = 'simple';
        }
        
        if (isset($def['option'])) {
            $option = $def['option'];
        }
        elseif(isset($def['options'])) {
            $option = $def['options'];
        }
        
        switch ($policy) {
            case 'delegate':
                $builder = new Builder\ViewModelDelegateBuilder($this->service);
                $overwrite = isset($option['overwrite']) ? (bool) $option['overwrite'] : false;
                $builder->setOverwrite($overwrite);
                break;
            case 'dispatch':
                $builder = new Builder\ViewModelDispatchBuilder($this->service);
                if (isset($option['controller'])) {
                    $builder->setControllerName($option['controller']);
                    unset($option['controller']);
                }
                elseif (isset($option['controllerName'])) {
                    $builder->setControllerName($option['controllerName']);
                    unset($option['controllerName']);
                }
                
                if (isset($def['signature'])) {
                    $builder->setSignature($def['signature']);
                }
                elseif (isset($option['signature'])) {
                    $builder->setSignature($option['signature']);
                    unset($option['signature']);
                }
                
                if (isset($option['dispatchOptions'])) {
                    $dispatchOptions = $option['dispatchOptions'];
                }
                elseif (isset($option['options'])) {
                    $dispatchOptions = $option['options'];
                }
                else {
                    $dispatchOptions = $option;
                }
                
                $builder->setDispatchOptions($dispatchOptions);
                    

                
                $builder->setServiceLocator($this->service->getServiceLocator());
                break;
            case 'class':
                $class = (string) @$def['class'];
                if (class_exists($class) && $class instanceof ViewModelBuilderInterface) {
                    $builder = new $class($this->service);
                }
                else {
                    $builder = new Builder\ViewModelBuilder($this->service);
                }
                break;
            case 'callback':
                $builder = new Builder\ViewModelCallbackBuilder($this->service);
                if (isset($option['callback'])) {
                    $builder->setCallback($option['callback']);
                }
                break;
            default:
            case 'simple':
                $builder = new Builder\ViewModelBuilder($this->service);
                break;
        }
        return $builder;
    }
}
