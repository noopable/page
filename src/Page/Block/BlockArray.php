<?php
namespace Page\Block;
class BlockArray extends Block implements BlockArrayInterface 
{

    protected $configArray;
    /**
     * configure this block
     * @see ProvidesResource
     * 
     * @param array $config
     * @return void
     */
    public function configure(array $config)
    {
        parent::configure($config);
        
        if (isset($config['blocks'])) {
            $this->setBlockArrayConfig($config['blocks']);
        }
        
    }
    
    public function setBlockArrayConfig(array $blockArrayConfig)
    {
        $this->configArray = (array) $blockArrayConfig;
    }
    
    public function getBlockArrayConfig()
    {
        return $this->configArray;
    }
    
    public function onInsert(BlockInterface $parent)
    {
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $block) {
                $parent->insertBlock($block);
            }
        }
        return false;
    }
    
    /**
     * 設定ベースで、Relayを実現するため設定をRelayBuilderへ引き渡す。
     * 
     * @return array
     */
    public function getBlockBuilder()
    {
        $blockBuilder = array(
            'class' => 'Page\Builder\BlockArrayBuilder',
            'options' => [],
        );
        if ($delegateBuilder = $this->getOption('blockBuilder', array())) {
            $blockBuilder['options']['delegateBuilder'] = $delegateBuilder;
        }
        return $blockBuilder;
    }
    
}
