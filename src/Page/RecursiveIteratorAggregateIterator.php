<?php
namespace Page;

/**
 * Description of RecursivePriorityQueue
 *
 * @author kosugi
 */
class RecursiveIteratorAggregateIterator implements \RecursiveIterator 
{
    /**
     *
     * @var \Iterator
     */
    protected $iterator;
    
    /**
     *
     * @var \IteratorAggregate
     */
    protected $iteratorAggregate;
    
    /**
     * 
     * @param \IteratorAggregate $iteratorAggregate
     */
    public function __construct(\IteratorAggregate $iteratorAggregate)
    {
        $this->iteratorAggregate = $iteratorAggregate;
        $this->iterator = $this->iteratorAggregate->getIterator();
    }

    public function current() {
        return $this->iterator->current();
    }

    public function getChildren() {
        return $this->current()->getChildren();
    }

    public function hasChildren() {
        return $this->current()->hasChildren();
    }

    public function key() {
        return $this->iterator->key();
    }

    public function next() {
        return $this->iterator->next();
    }

    public function rewind() {
        $this->iterator = $this->iteratorAggregate->getIterator();
    }

    public function valid() {
        return $this->iterator->valid();
    }
}
