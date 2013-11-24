<?php
namespace Page\Block;

/**
 *
 * @author tomoaki
 */
interface BlockArrayInterface extends BlockInterface{
    public function setBlockArrayConfig(array $blockArrayConfig);
    
    /**
     * 
     * @return array
     */
    public function getBlockArrayConfig();
}

