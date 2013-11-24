<?php
namespace Page\Block;
use Zend\View\Model\ViewModel;

use Page\Builder\BlockBuilderInterface;
use Page\ViewModel\Builder\ViewModelBuilderInterface;
/**
 *
 * @author tomoaki
 */
interface BlockInterface {

    /**
     * 
     * @param array $config
     */
    public function configure(array $config);
    
    /**
     * 
     * @param \Page\Block\BlockInterface $parent
     */
    public function setParent(BlockInterface $parent);
    
    /**
     * 
     * BlockInterface
     */
    public function getParent();

    public function digByName($name);

    public function byName($name = null);

    public function insertBlock(BlockInterface $block);

    public function removeBlock(BlockInterface $block);
    
    public function onInsert(BlockInterface $parent);

    public function setOrder($order);

    public function getOrder();

    /**
     * 
     * @param \Page\Builder\BlockBuilderInterface $blockBuilder
     */
    public function setBlockBuilder(BlockBuilderInterface $blockBuilder);
    
    public function getBlockBuilder();
    
    /**
     * 
     * @param \Page\ViewModel\Builder\ViewModelBuilderInterface $viewModelBuilder
     */
    public function setViewModelBuilder(ViewModelBuilderInterface $viewModelBuilder);
    
    public function getViewModelBuilder();

    /**
     * 名称はtemplateが実質的にviewScriptのパス
     * 
     * @param string $template
     */
    public function setTemplate($template);

    /**
     * 
     * @return string
     */
    public function getTemplate();

    public function setViewModel(ViewModel $viewModel = null);

    public function getViewModel();
    
    public function getState();
}

