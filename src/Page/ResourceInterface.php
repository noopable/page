<?php
namespace Page;

use Zend\Permissions\Acl;

interface ResourceInterface extends Acl\Resource\ResourceInterface
{
    public function getResourceClass();
    public function getResourceId();
    
}