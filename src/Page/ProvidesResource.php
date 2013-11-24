<?php
namespace Page;

use Zend\Stdlib\ArrayUtils;

trait ProvidesResource
{
    public $name = '';

    public $class;

    static public $_nameDelimiter = '::';

    protected $options = array();
    
    protected $properties = array();
    
    protected $prototype;

    public function configureResource(array $config)
    {
        foreach ($config as $key => $value)
        {
            switch ($key) {
                case "name":
                    $this->setName($value);
                    break;
                case "class":
                    $this->setResourceClass($value);
                    break;
                case "options":
                    $this->setOptions($value);
                    break;
                case "properties":
                    $this->setProperties($value);
                    break;
                case "defaultOptions":
                    $this->setDefaultOptions($value);
                    break;
                case "prototype":
                    $this->setPrototype($value);
                    break;
                default:
                    $this->setProperty($key, $value);
                    break;
            }
        }
        return $this;
    }
    
    public function getResourceId()
    {
        if (strlen($name)) {
            return $this->getResourceClass() & self::_nameDelimiter & $this->getName();
        }
        else {
            return $this->getResourceClass();
        }

    }

    public function setName($name)
    {
        $this->name = (string) $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setResourceClass($class)
    {
        $this->class = (string) $class;
    }

    public function getResourceClass()
    {
        if (!isset($this->class)) {
            $this->class = get_class($this);
        }

        return $this->class;
    }

    public function setPrototype($prototype)
    {
        $this->prototype = clone $prototype;
        $this->onInjectPrototype();
    }

    public function onInjectPrototype(){}

    /**
     * Set a single option
     *
     * @param  string $name
     * @param  mixed $value
     * @return ViewModel
     */
    public function setOption($name, $value)
    {
        $this->options[(string) $name] = $value;
        return $this;
    }

    /**
     * Get a single option
     *
     * @param  string       $name           The option to get.
     * @param  mixed|null   $default        (optional) A default value if the option is not yet set.
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        $name = (string)$name;
        if (! array_key_exists($name, $this->options)) {
            if (isset($this->prototype) && $this->prototype instanceof AbstractResource) {
                return $this->prototype->getOption($name, $default);
            }
            else {
                return $default;
            }
        }
        return $this->options[$name];
    }

    /**
     * Set renderer options/hints en masse
     *
     * @param array|\Traversable $options
     * @throws \Zend\View\Exception\InvalidArgumentException
     * @return self
     */
    public function setOptions($options, $clear = false)
    {
        // Assumption is that lowest common denominator for renderer configuration
        // is an array
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects an array, or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }
        if ($clear) {
            $this->options = $options;
        }
        else {
            $this->options = ArrayUtils::merge($this->options, $options);
        }

        return $this;
    }

    /**
     * Set renderer options/hints en masse
     *
     * @param array|\Traversable $defaultOptions
     * @throws \Zend\View\Exception\InvalidArgumentException
     * @return self
     */
    public function setDefaultOptions($defaultOptions)
    {
        // Assumption is that lowest common denominator for renderer configuration
        // is an array
        if ($defaultOptions instanceof Traversable) {
            $defaultOptions = ArrayUtils::iteratorToArray($defaultOptions);
        }

        if (!is_array($defaultOptions)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects an array, or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($defaultOptions) ? get_class($defaultOptions) : gettype($defaultOptions))
            ));
        }

        $this->options = ArrayUtils::merge($defaultOptions, $this->options);
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setProperties($properties, $clear = false)
    {
        // Assumption is that lowest common denominator for renderer configuration
        // is an array
        if ($properties instanceof Traversable) {
            $properties = ArrayUtils::iteratorToArray($properties);
        }

        if (!is_array($properties)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects an array, or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($properties) ? get_class($properties) : gettype($properties))
            ));
        }
        if ($clear) {
            $this->properties = $properties;
        }
        else {
            $this->properties = ArrayUtils::merge($this->properties, $properties);
        }

        return $this;
    }

    public function getProperties($withPrototype = false)
    {
        if (! $withPrototype || ! isset($this->prototype)) {
            return $this->properties;
        }
        
        $protoProperties = $this->prototype->getProperties(true);
        return ArrayUtiles::merge($protoProperties, $this->properties);
    }
    
    public function issetProperty($name, $recursive = true)
    {
        if (isset($this->properties[$name])) {
            return true;
        }
        
        if (! $recursive) {
            return false;
        }
        
        if (isset($this->prototype) && $this->prototype->issetProperty($name, true)) {
            return true;
        }
        
        return false;
    }

    public function getProperty($name, $default = null)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        
        if (isset($this->prototype)) {
            return $this->prototype->getProperty($name, $default);
        }
        
        return $default;
    }

    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }
}