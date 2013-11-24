<?php
namespace Page\Block;
use Page\Exception;
/**
 * Options => 'ignore_error_level'
 *            'delegate_error_level'
 * 
 *
 * @author tomoaki
 */
class BlockError extends Block{

    protected $errors = [];
    
    protected $exceptions = [];
    
    protected $renderStrategy;
    
    public function addException(\Exception $e)
    {
        $this->exceptions[] = $e;
    }
    
    public function getExeceptions()
    {
        return $this->exceptions;
    }
    
    public function addError($message, $level)
    {
        $this->errors[] = ['message' => $message, 'level' => $level];
    }
    
    public function getErrors()
    {
        return $this->errors;
    }

    public function setRenderStrategy($renderStrategy)
    {
        $this->renderStrategy = $renderStrategy;
    }
}
