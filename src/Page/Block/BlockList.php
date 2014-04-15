<?php
namespace Page\Block;

use Page\Exception;

use Countable;
use IteratorAggregate;
use Serializable;

class BlockList implements Countable, IteratorAggregate, Serializable
{
    protected $innerIterator;

    protected $parent;

    protected $recursiveIterator;

    public function __construct(BlockInterface $parent)
    {
        $this->parent = $parent;
    }

    public function getBlockListIterator()
    {
        if (!isset($this->recursiveIterator)) {
            $this->recursiveIterator = new BlockListIterator($this);
        }
        return clone $this->recursiveIterator;
    }

    public function issetName($name)
    {
        return isset($this->items[$name]);
    }

    public function byName($name = null)
    {
        if (null === $name) {
            return $this->items;
        }

        if (isset($this->items[$name])) {
            return $this->items[$name]['data'];
        }

        return null;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function insert(BlockInterface $block)
    {
        $priority = (int) $block->getOrder();
        $name = (string) $block->getName();

        $this->items[$name] = array(
            'data'     => $block,
            'priority' => $priority,
        );
        $this->getQueue()->insert($block, $priority);
        return $this;
    }

    public function remove($block)
    {
        if ($block instanceof Block) {
            return $this->removeBlock($block);
        }

        if (is_string($block)) {
            return $this->removeByName($block);
        }

    }

    public function removeByName($name)
    {
        if (isset($this->items[$name])) {
            $datum = $this->items[$name]['data'];
        }

        return $this->removeBlock($datum);

    }

    public function exchangeArray($array)
    {
        $newArray = array_filter($array, function ($var){return ($var instanceof Block);});
        foreach ($newArray as $block) {
            $this->insert($block);
        }
        return $newArray;
    }

    const EXTR_DATA     = 0x00000001;
    const EXTR_PRIORITY = 0x00000002;
    const EXTR_BOTH     = 0x00000003;

    /**
     * Inner queue class to use for iteration
     * @var string
     */
    protected $queueClass = 'Zend\Stdlib\SplPriorityQueue';

    /**
     * Actual items aggregated in the priority queue. Each item is an array
     * with keys "data" and "priority".
     * @var array
     */
    protected $items      = array();

    /**
     * Inner queue object
     * @var SplPriorityQueue
     */
    protected $queue;

    /**
     * Remove an item from the queue
     *
     * This is different than {@link extract()}; its purpose is to dequeue an
     * item.
     *
     * This operation is potentially expensive, as it requires
     * re-initialization and re-population of the inner queue.
     *
     * Note: this removes the first item matching the provided item found. If
     * the same item has been added multiple times, it will not remove other
     * instances.
     *
     * @param  mixed $datum
     * @return bool False if the item was not found, true otherwise.
     */
    public function removeBlock($datum)
    {
        $found = false;
        foreach ($this->items as $key => $item) {
            if ($item['data'] === $datum) {
                $found = true;
                break;
            }
        }
        if ($found) {
            unset($this->items[$key]);
            $this->queue = null;

            if (!$this->isEmpty()) {
                $queue = $this->getQueue();
                foreach ($this->items as $item) {
                    $queue->insert($item['data'], $item['priority']);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Is the queue empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (0 === $this->count());
    }

    /**
     * How many items are in the queue?
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Peek at the top node in the queue, based on priority.
     *
     * @return mixed
     */
    public function top()
    {
        return $this->getIterator()->top();
    }

    /**
     * Extract a node from the inner queue and sift up
     *
     * @return mixed
     */
    public function extract()
    {
        return $this->getQueue()->extract();
    }

    /**
     * Retrieve the inner iterator
     *
     * SplPriorityQueue acts as a heap, which typically implies that as items
     * are iterated, they are also removed. This does not work for situations
     * where the queue may be iterated multiple times. As such, this class
     * aggregates the values, and also injects an SplPriorityQueue. This method
     * retrieves the inner queue object, and clones it for purposes of
     * iteration.
     *
     * @return SplPriorityQueue
     */
    public function getIterator()
    {
        $queue = $this->getQueue();
        return clone $queue;
    }

    /**
     * Serialize the data structure
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * Unserialize a string into a PriorityQueue object
     *
     * Serialization format is compatible with {@link Zend\Stdlib\SplPriorityQueue}
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }

    /**
     * Serialize to an array
     *
     * By default, returns only the item data, and in the order registered (not
     * sorted). You may provide one of the EXTR_* flags as an argument, allowing
     * the ability to return priorities or both data and priority.
     *
     * @param  int $flag
     * @return array
     */
    public function toArray($flag = self::EXTR_DATA)
    {
        switch ($flag) {
            case self::EXTR_BOTH:
                return $this->items;
                break;
            case self::EXTR_PRIORITY:
                return array_map(function ($item) {
                    return $item['priority'];
                }, $this->items);
            case self::EXTR_DATA:
            default:
                return array_map(function ($item) {
                    return $item['data'];
                }, $this->items);
        }
    }

    /**
     * Specify the internal queue class
     *
     * Please see {@link getIterator()} for details on the necessity of an
     * internal queue class. The class provided should extend SplPriorityQueue.
     *
     * @param  string $class
     * @return PriorityQueue
     */
    public function setInternalQueueClass($class)
    {
        $this->queueClass = (string) $class;
        return $this;
    }

    /**
     * Does the queue contain the given datum?
     *
     * @param  mixed $datum
     * @return bool
     */
    public function contains($datum)
    {
        foreach ($this->items as $item) {
            if ($item['data'] === $datum) {
                return true;
            }
        }
        return false;
    }

    /**
     * Does the queue have an item with the given priority?
     *
     * @param  int $priority
     * @return bool
     */
    public function hasPriority($priority)
    {
        foreach ($this->items as $item) {
            if ($item['priority'] === $priority) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the inner priority queue instance
     *
     * @throws \DomainException
     * @return SplPriorityQueue
     */
    protected function getQueue()
    {
        if (null === $this->queue) {
            $this->queue = new $this->queueClass();
            if (!$this->queue instanceof \SplPriorityQueue) {
                throw new \DomainException(sprintf(
                    'PriorityQueue expects an internal queue of type SplPriorityQueue; received "%s"',
                    get_class($this->queue)
                ));
            }
        }
        return $this->queue;
    }

    /**
     * Add support for deep cloning
     *
     * @return void
     */
    public function __clone()
    {
        if (null !== $this->queue) {
            $this->queue = clone $this->queue;
        }
    }
}