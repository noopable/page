<?php
namespace Page;
/*
 *
 */
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use \SplObjectStorage;

use Page\Block\BlockInterface;
/**
 * Description of BlockInitializer
 *
 * @author tomoaki
 */
class BlockInitializer implements InitializerInterface, ServiceLocatorAwareInterface {
    use ServiceDependency;
    use ServiceLocatorAwareTrait;
    /**
     *
     * @var SplObjectStorage
     */
    protected $in_process;

    protected $creationOptions;

    /**
     *
     * @var BlockPluginManager
     */
    protected $blocks;

    public function __construct(Service $service)
    {
        $this->service = $service;
        $this->in_process = new SplObjectStorage();
        $this->instances = array();
        $this->creationOptions = array();
    }

    /**
     *
     * @param type $options
     */
    public function setCreationOptions($options)
    {
        $this->creationOptions = $options;
    }

    public function initialize($block, ServiceLocatorInterface $serviceLocator){
        if (! $block instanceof BlockInterface) {
            throw new Exception\RuntimeException(sprintf(
                'Plugin of type %s is invalid; must implement Page\Block\BlockInterface',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                __NAMESPACE__
            ));
        }

        if ($this->in_process->offsetExists($block)) {
            throw new Exception\RuntimeException('speicified block is now constructing. a recursion detected. check your block build configuration');
        }

        $name = @$this->creationOptions['name'];

        $this->in_process->attach($block, true);

        $state = $block->getState();
        try {
            if (! $state->checkFlag($state::CONFIGURED)) {
                $block->configure($this->creationOptions);
                $state->setFlag($state::CONFIGURED);
            }

            $this->creationOptions = array();

            if (! $state->checkFlag($state::INITIALIZED)) {
                $builder = $this->service->getBuilderFactory()
                            ->getBlockBuilderFromBlock($block);
                /**
                 *
                 */
                if ($builder instanceof Builder\BlockBuilderInterface) {
                    $builder->build($block);
                }
                $state->setFlag($state::INITIALIZED);
            }

        }
        catch (\Exception $e) {
            unset($this->in_process);
            $this->in_process = new SplObjectStorage();
            throw $e;
        }

        $this->in_process->detach($block);
    }

    public function getBlocks()
    {
        if (!isset($this->blocks)) {
            $this->blocks = $this->service->getBlocks();
        }
        return $this->blocks;
    }
}
