<?php
namespace Page;

use Zend\Stdlib\ArrayUtils;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

use Page\Block\BlockInterface;

class Service
{
    use ServiceSetterGetter;
    use ProvidesConfigId;

    /**
     *
     * @var array
     */
    protected $pages;

    /**
     *
     * @var string currentPageName
     */
    protected $currentPage;

    /**
     *
     * @var array
     */
    protected $blockConfig = array();

    /**
     *
     * @var bool
     */
    protected $init = false;

    /**
     *
     * @var bool
     */
    protected $active = false;

    protected $originalRouteMatch;


    public function init()
    {
        if ($this->init) {
            return;
        }
        $serviceLocator = $this->getServiceLocator();


        //ここでconfigを取得する必要があるかもしれない。
        //もしくは、initializerをFactoryに移すという考え方もありうる。どうする？
        $config = $serviceLocator->get('Config')[$this->getConfigId()];

        $blockPlugins = $serviceLocator->get($config['blocks_service_name']);

        $this->setBlocks($blockPlugins);

        if (isset($config['config_loader'])) {
            $configLoader = $serviceLocator->get($config['config_loader_name']);
            if ($configLoader instanceof \Page\Config\Loader\ConfigLoaderInterface) {
                $this->setConfigLoader($configLoader);
            }
        }

        if (isset($config['error_page'])) {
            $this->setErrorPage($config['error_page']);

            $application    = $serviceLocator->get('Application');
            $eventManager   = $application->getEventManager();

            //Service がinitされ、なおかつerror_pageが設定されているときだけ
            //エラーハンドリングを行う。
            //エラーページを切り替えたい場合は、コントローラーやページから
            //setErrorPageで切りかえる。
            //ただし、エラーハンドリングは十分にテストし、十分にシンプルであるように心掛けること。

        }

        $this->init = true;
    }

    public function subscribe($eventManager)
    {
        $eventManager->attachAggregate($this->getSubscriber());
    }

    public function onRoute(MvcEvent $e)
    {
        if ($requestedName = $e->getRouteMatch()->getParam('page', false)) {
            //サービスファクトリはインスタンスだけ保持しててくれれば。
            //モジュールから呼んでもらえる前提なら、
            $this->activate($requestedName);
            $events = $e->getApplication()->getEventManager();
            $this->getSubscriber()->utilizeSharedEvent($events);
        }
    }

    public function activate($requestedName)
    {
        $this->init();
        $this->setRequestedName($requestedName);
        $this->loadPage($requestedName);
        $this->active = true;
    }

    public function isActivated()
    {
        return $this->active;
    }

    public function loadBlockConfig($name, $merge = true)
    {
        //DBからの読み込みを追加・交換する場合、
        //ConfigLoaderを入れ替えるか、EventFulにして追加・交換を可能にする。
        $config = array();
        $configLoader = $this->getConfigLoader();
        if ($configLoader instanceof Config\Loader\ConfigLoaderInterface) {
            $config = $configLoader->load($name);
        }

        if ($merge) {
            $origConfig = $this->getBlockConfig($name);
            $config = ArrayUtils::merge($origConfig, $config);
        }
        $config['name'] = $name;
        $this->setBlockConfig(array($name => $config));
        return $config;
    }
    /**
     *
     *
     * @param string $name
     * @return \Page\Page
     */
    public function loadPage($name)
    {
        $this->init();
        try {
            $page = $this->getBlock($name, ['depth' => 0]);
        }
        catch (Exception\DuplicatedBlockNameException $e) {
            $thrownBlockName = $e->getBlockName();
            $parentBlock = $e->getParent();
            trigger_error($e->getMessage(), E_USER_WARNING);
            //for instant debug
            echo "\n<!-- duplicate entry $thrownBlockName in block(" .$parentBlock->getName() . ") -->\n";
            return null;
        }
        catch (Exception\InvalidArgumentException $e) {
            //for instant debug
            echo $e;
            return null;
        }
        catch (\Zend\Di\Exception\RuntimeException $e) {
            //for instant debug
            $serviceLocator = $this->getServiceLocator();
            if ($serviceLocator->has('Di')) {
                $di = $serviceLocator->get('Di');
                \Zend\Di\Display\Console::export($di);
            }
            //for instant debug
            echo $e;
            return null;
        }
        catch (\Exception $e) {
            //for instant debug
            echo $e;
            return null;
        }

        if (! $page instanceof BlockInterface) {
            throw new Exception\UnexpectedValueException("page load faild or unexpected type ($name)");
        }

        $this->pages[$name] = $page;
        return $page;
    }

    public function getBlock($name, $config = array(), $loadConfig = true)
    {
        if ((false !== $config) && $loadConfig) {
            $this->loadBlockConfig($name);
            $config = ArrayUtils::merge($this->getBlockConfig($name), (array) $config);
        }

        return $this->getBlocks()->get($name, $config);
    }

    public function getBlockConfig($name)
    {
        $config =  isset($this->blockConfig[$name]) ? $this->blockConfig[$name] : array();
        return $config;
    }

    public function setBlockConfig(array $config, $merge = true)
    {
        if ($merge && count($this->blockConfig)) {
            $config = ArrayUtils::merge($this->blockConfig, $config);
        }
        $this->blockConfig = $config;

        return $this;
    }

    public function getPage($name = null)
    {
        $this->init();
        if (null === $name) {
            $name = $this->getCurrentPage();
        }

        if (! isset($this->pages[$name])) {
            $page = $this->loadPage($name);
            if (null === $page) {
                throw new Exception\UnexpectedValueException("page load faild ($name)");
            }

            if (! $page instanceof BlockInterface) {
                throw new Exception\UnexpectedValueException("unexpected type ($name)");
            }
        }

        return $this->pages[$name];
    }

    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * エラーが発生するとエラーページに切り替わることがある
     *
     * @return string
     */
    public function getCurrentPage()
    {
        if (!isset($this->currentPage)) {
            $this->currentPage = $this->getRequestedName();
        }
        return $this->currentPage;
    }

    /**
     *
     * @return ViewModel
     */
    public function buildViewModel()
    {
        $director = $this->getViewModelBuildDirector();
        $model = $director->build();
        $this->utilizeViewHelpers();
        return $model;
    }

    /**
     *
     * @deprecated
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function preDispatch(MvcEvent $e)
    {
        //実際には、InjectViewModelListenerはdetachしなくてもよさそう。
        //なぜならpostDispatchで強制的に投入されるのと、
        //必要なViewModelはinjectされる前に回収されるから。
        if ($this->isActivated()) {
            //$this->getSubscriber()->detachProblemListeners($e);
        }
    }

    public function postDispatch(MvcEvent $e)
    {
        if ($this->isActivated()) {
            $viewModel = $this->buildViewModel();
            $e->setResult($viewModel);
            $e->setViewModel($viewModel);
        }
    }

    protected function utilizeViewHelpers()
    {
        $page = $this->getPage();
        $hm = $this->getServiceLocator()->get('ViewHelperManager');

        if ($title = $page->getProperty('title', false)) {
            $headTitleHelper = $hm->get('headTitle');
            $headTitleHelper($title);
        }

    }

    public function setTemplate(BlockInterface $block, $template)
    {
        $block->setOption('template', $template);
    }

    public function getTemplate(BlockInterface $block, $detect = false, $delimiter = '/')
    {
        if (! $detect) {
            return $block->getTemplate();
        }

        if (! $template = $block->getOption('template', false)) {
            $template = $this->detectTemplate($block, $delimiter);
        }

        return $template;
    }

    public function detectTemplate(BlockInterface $block)
    {
        $policy = $block->getOption('templatePathPolicy', null);
        $delimiter = $block->getOption('templatePathDelimiter', $delimiter);
        $root = $block->getOption('templatePathRoot', 'pages');
        switch($policy) {
            case "path":
                $template = $block->getName();
                $block = $block;
                while($block = $block->getParent()) {
                    $template = $block->getName() . $delimiter . $template;
                }
                $template = $root . '/' . $template; // for util. this is root directory
                break;
            case "callback":
                if (is_callable($template)) {
                    $template = $template($block);
                    break;
                }
            case "tail":
            default:
                if ($parent = $block->getParent()) {
                    $template = $parent->getTemplate($delimiter) . $delimiter . $block->getName();
                }
                else {
                    $template = $root . '/' . $block->getName();
                }
                break;
        }
    }

    public function getOriginalRouteMatch()
    {
        if (!isset($this->originalRouteMatch)) {
            $this->originalRouteMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        }
        return $this->originalRouteMatch;
    }

    public function onDispatchError(MvcEvent $e)
    {
        $this->init();

        $this->setCurrentPage($this->getErrorPage());
        $pageViewModel = $this->buildViewModel();
        //$this->injectViewModel($e);

        $result = $e->getResult();
        if ($result instanceof ViewModel) {
            $pageViewModel->addChild($result);
        }

        $e->setResult($pageViewModel);

    }

    public function onRenderError(MvcEvent $e)
    {
        $this->init();

        $this->setCurrentPage($this->getErrorPage());
        $pageViewModel = $this->buildViewModel();
        //$this->injectViewModel($e);

        if ($e->getError() && $pageViewModel instanceof ClearableModelInterface) {
            $pageViewModel->clearChildren();
        }

        $result = $e->getResult();
        if ($result instanceof ViewModel) {
            $pageViewModel->addChild($result);
        }

        //$pageViewModel->setTerminal(true);
        $e->setResult($pageViewModel);

    }

}

