<?php
namespace Page\Block;
use Page\RecursiveIteratorAggregateIterator;
/**
 * Description of BlockListIterator
 *
 * @author tomoaki
 */
class BlockListIterator extends RecursiveIteratorAggregateIterator {
    
    protected $parent;
    
    public function setParent(BlockInterface $parent)
    {
        $this->parent = $parent;
    }
    
    public function getParent()
    {
        if (!isset($this->parent) && method_exists($this->iteratorAggregate, 'getParent')) {
            $this->parent = $this->iteratorAggregate->getParent();
        }
        return $this->parent;
    }
}
