<?php
namespace Page\Block;

/**
 *
 * @author tomoaki
 */
class Context {
    
    public $context = '/';
    //put your code here
    public function __construct($context)
    {
        $this->context = $context;
    }
    
    public function __toString() {
        return (string) $this->context;
    }
}

