<?php
namespace Page\Builder;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

use Page\Exception;
use Page\Block\BlockInterface;
/**
 * Description of BlockArrayBuilder
 *
 * @author tomoaki
 */
class BlockArrayBuilder extends BlockBuilder implements ServiceLocatorAwareInterface 
{
    use ServiceLocatorAwareTrait;
    use DelegateBlockBuilderTrait;
    
    protected $isDelegate = true;
    
    /**
     * 
     * @param \Page\Block\BlockInterface $block
     * @return \Page\Block\BlockInterface
     * @throws Exception\RuntimeException
     */
    public function build(BlockInterface $block){
        $this->block = $block;
        
        if ($this->isDelegate() && $delegateBuilder = $this->getDelegateBuilder()) {
            $delegateBuilder->build($block);
            return $block;
        }
        
        if($block instanceof \Page\Block\BlockArrayInterface) {
            $arrayConfig = $this->block->getBlockArrayConfig();
            if (!is_array($arrayConfig)) {
                trigger_error('no array config', E_USER_WARNING);
                return $block;
            }
            foreach ($arrayConfig as $k => $v) {
                $name   = 'block';
                $config = array();
                $load   = false;
                if (is_array($v)) {
                    if (is_string($k)) {
                        $name = $v['name'] = $k;
                    }
                    elseif(isset($v['name'])) {
                        $name = $v['name'];
                    }
                    
                    if (isset($v['config'])) {
                        $config = $v['config'];
                        $config['name'] = $name;
                    }
                    else {
                        $config = $v;
                    }
                    
                    if (isset($v['load'])) {
                        $load = (bool) $v['load'];
                    }
                }
                elseif (is_string($v)) {
                    $name = $v;
                    $load = true;
                }
                
                $child = $this->service->getBlock($name, $config, $load);
                $block->insertBlock($child);
            }
            return $block;
        }
        
        throw new Exception\RuntimeException(__CLASS__ . ' depends on \Page\BlockArrayInterface');
        
    }
    
    public function setIsDelegate($delegate)
    {
        $this->isDelegate = (bool) $delegate;
    }
        
    public function isDelegate()
    {
        return $this->isDelegate;
    }
            
}
