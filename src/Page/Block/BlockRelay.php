<?php
namespace Page\Block;

/**
 * Description of BlockRelay
 *
 * @author tomoaki
 */
class BlockRelay extends Block {

    /**
     * 設定ベースで、Relayを実現するため設定をRelayBuilderへ引き渡す。
     * 
     * @return string
     */
    public function getBlockBuilder()
    {
        $config = array(
            'options' => ['delegateBuilder' => $this->getOption('blockBuilder', array())],
            'class' => 'Page\Builder\BlockRelayBuilder',
        );
        
        return $config;
    }
}
