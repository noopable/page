<?php
namespace Page;

use Serializable;
/**
 * Description of State
 *
 * @author tomoaki
 */
final class State implements Serializable{
    
    const CONFIGURED = 'CONFIGURED';
    const INITIALIZED = 'INITIALIZED';
    const PREPARE_VIEW_MODEL = 'PREPARE_VIEW_MODEL';
    
    protected $values = array(
        'CONFIGURED' => false,
        'INITIALIZED' => false,
        'PREPARE_VIEW_MODEL' => false,
    );
    
    public function __construct(array $states = null)
    {
        if (is_array($states)) {
            $this->values = $states;
        }
    }
    
    public function checkFlag($flag)
    {
        return (isset($this->values[$flag]) && $this->values[$flag]);
    }
    
    public function setFlag($flag, $bool = true)
    {
        $this->values[$flag] = (bool) $bool;
    }

    public function serialize() {
        $values = array();
        foreach ($this->values as $k => $v) {
            if ($v) {
                $values[] = $k;
            }
        }
        return serialize($values);
    }

    public function unserialize($serialized) {
        $values = array_fill_keys(unserialize($serialized), true);
        $this->values = array_merge($this->values, $values);
    }
}
