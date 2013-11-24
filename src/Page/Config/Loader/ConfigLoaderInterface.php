<?php
namespace Page\Config\Loader;

/**
 * Description of ConfigLoader
 *
 * @author tomoaki
 */
interface ConfigLoaderInterface {
    
    public function __construct(array $config = null);
    public function load($name);
}
