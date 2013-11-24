<?php
namespace Page\Exception;

/**
 * Description of DuplicatedBlockNameException
 *
 * @author tomoaki
 */
class DuplicatedBlockNameException extends InvalidArgumentException{
    //put your code here
    public function setBlockName($blockName)
    {
        $this->blockName = $blockName;
    }
    
    public function getBlockName()
    {
        return $this->blockName;
    }
    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
}
