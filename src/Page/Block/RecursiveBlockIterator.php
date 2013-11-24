<?php
namespace Page\Block;
/**
 * Description of RecursiveBlockIterator
 *
 * @author tomoaki
 */
trait RecursiveBlockIterator {

    public $childBlocks;
    
    protected $_iterate = true;
    
    public function getChildBlocks()
    {
        if (!isset($this->childBlocks)) {
            $this->childBlocks = new BlockList($this);
        }
        return $this->childBlocks;
    }
    /**
     * RecursiveIteratorInterface
     *
     */
    public function getChildren ()
    {
        return $this->getChildBlocks()->getBlockListIterator();
    }

    public function hasChildren ()
    {
        if (isset($this->childBlocks) && ! $this->childBlocks->isEmpty()) {
            return true;
        }
        return false;
    }
    
    public function current ()
    {
        return $this;
    }

    public function key ()
    {
        return $this->getName();
    }

    public function next ()
    {
        $this->_iterate = false;
    }

    public function rewind ()
    {
        $this->_iterate = true;
    }

    public function valid ()
    {
        return $this->_iterate;
    }
}
