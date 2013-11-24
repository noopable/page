<?php
namespace Page;


/**
 * Description of ServiceDepInterface
 *
 * @author tomoaki
 */
trait ServiceDependency {
    
    protected $service;
    /**
     * 
     * 
     * @param \Page\Service $service Page moduleのServiceLayer
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    
}
