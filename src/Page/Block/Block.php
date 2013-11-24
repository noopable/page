<?php

namespace Page\Block;

use Zend\View\Model\ViewModel;
use Zend\Stdlib\ArrayUtils;

use RecursiveIterator;

use Page\State;// final
use Page\Exception;
use Page\ProvidesResource;
use Page\Builder\BlockBuilderInterface;
use Page\ViewModel\Builder\ViewModelBuilderInterface;
/**
 * ドキュメントを表現するblockオブジェクト
 * 
 * parentは循環参照になるので、シリアライズしない。
 * その代わりビルドを行う必要が出てくる？
 *
 * @author tomoaki
 *
 */
class Block implements BlockInterface, \Page\ResourceInterface, RecursiveIterator 
{
    use ProvidesResource;
    use RecursiveBlockIterator;
    
    public $viewModel;

    /**
     * parent in the block tree
     * @var \Page\Block\BlockInterface
     */
    private $parent = null;
    
    protected $viewModelBuilder;
    
    protected $template;
    
    protected $maxDepth = 20;

    /**
     * block order level in parent block
     * 
     * @var int
     */
    public $order;
    
    /**
     * block nest level
     * 
     * @var int
     */
    public $depth;
    
    protected $init = false;
    
    
    /**
     * configure this block
     * @see ProvidesResource
     * 
     * @param array $config
     * @return void
     */
    public function configure(array $config)
    {
        $this->configureResource($config);
        if (isset($config['order'])) {
            $this->setOrder((int) $config['order']);
        }
        
        if (isset($config['depth'])) {
            $this->setDepth((int) $config['depth']);
        }
        
        if (isset($config['template'])) {
            $this->setTemplate((string) $config['template']);
        }
        $state = $this->getState();
        $state->setFlag($state::CONFIGURED);
    }
       
    public function setParent(BlockInterface $block)
    {
        return $this->parent = $block;
    }
    
    public function getParent()
    {
        return $this->parent;
    }

    public function digByName($name)
    {
        $aName = explode('/', (string) $name, 2);
        $name = array_shift($aName);
        $block = $this->byName($name);

        if (! count($aName)) {
            return $block;
        }

        if (! $block instanceof Block) {
            return null;
        }

        return $block->digByName(array_shift($aName));

    }

    /**
     * 
     * @param string $name
     * @return null|BlockInterface
     */
    public function byName($name = null)
    {
        if ($this->hasChildren()) {
            return $this->getChildBlocks()->byName($name);
        }

        return null;
    }

    public function insertBlock(BlockInterface $block)
    {
        if ($block->onInsert($this)) {
            $this->getChildBlocks()->insert($block);
        }
        return $this;
    }

    public function removeBlock(BlockInterface $block)
    {
        $this->getChildren()->remove($block);
    }

    public function onInsert(BlockInterface $parent)
    {
        $this->setParent($parent);
        $this->setDepth($parent->getDepth() + 1);
        // ok. go add children
        return true;
    }
    /**
     * 
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * 
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * 
     * @param int $depth
     */
    public function setDepth($depth)
    {
        if ($depth > $this->maxDepth) {
            throw new Exception\RuntimeException('max depth proceeded');
        }
        $this->depth = $depth;
    }
    
    /**
     * 
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    public function setBlockBuilder(BlockBuilderInterface $blockBuilder) {
        $this->blockBuilder = $builder;
    }
    
    public function getBlockBuilder() {
        //具象ページクラスではこの部分でブロック定義を書いてもよい。
        //クラスを作るときはクラスから、クラスを書かないときは、無名関数おcallbackBuilderを使う
        if (isset($this->blockBuilder)) {
            return $this->blockBuilder;
        }
        return null;
    }
    
    public function setViewModelBuilder(ViewModelBuilderInterface $viewModelBuilder)
    {
        $this->viewModelBuilder = $builder;
    }
    
    public function getViewModelBuilder()
    {
        if (isset($this->viewModelBuilder)) {
            return $this->viewModelBuilder;
        }
        
        return null;
    }

    /**
     * 名称はtemplateが実質的にviewScriptのパス
     * 
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * templateが設定されていないときのケアはサービス側で行う。
     * リソースは感知しない。
     * 
     * @return string
     */
    public function getTemplate()
    {
        if (isset($this->template)) {
            return $this->template;
        }
        return $this->getOption('template', null);
    }

    public function setViewModel(ViewModel $viewModel = null)
    {
        if (null === $viewModel) {
            $viewModel = new ViewModel;
        }
        
        $this->viewModel = $viewModel;

        return $this;
    }

    public function getViewModel()
    {
        if (!isset($this->viewModel)) {
            $this->setViewModel();
        }
        return $this->viewModel;
    }
    
    public function getState()
    {
        if (!isset($this->state)) {
            $this->state = new State;
        }
        return $this->state;
    }

    public function __sleep()
    {
        /**
         * @WIP
         * 
         * @var array $suppressed
         */
        $suppressed = array('parent', 'events');
        return array_diff(array_keys(get_object_vars($this)), $suppressed);
    }
    
    public function __wakeup()
    {
        $this->service->wakeupBlock($this);
    }
}