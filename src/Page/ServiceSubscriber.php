<?php
namespace Page;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
/**
 * Description of ServiceSubscriber
 *
 * @author tomoaki
 */
class ServiceSubscriber implements ListenerAggregateInterface {
    
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     *
     * @var \Page\Service
     */
    protected $service;
    
    protected $sharedEventAttached;
    
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @param  integer $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this->service, 'onRoute'), $priority);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this->service, 'postDispatch'), -10); //postDispatch
        
        // @deprecated
        // $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this->service, 'preDispatch'), 5);
        
        /**
         * 
         * @see \Zend\Mvc\View\Http\ExceptionStrategy
         *         $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareExceptionViewModel'));
         *         $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'prepareExceptionViewModel'));
         * 
         * ExceptionStrategyは例外処理に関して、例外処理用のテンプレートを使って、
         * 例外を含むViewModelを準備する。
         * 
         * @see \Zend\Mvc\View\HttpViewManager
         *         $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($injectViewModelListener, 'injectViewModel'), -100);
         *         $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($injectViewModelListener, 'injectViewModel'), -100);
         * 
         * injectViewModelで行われるのは、EventResultがViewModelなら、そのViewModelの子モデルを削除して、
         * EventのViewModelにappendする。
         * 
         * 
         * Pageモジュールは、EventのViewModelを置き換えており、自前でinjectできる。
         * メインのエラー処理部分は、ExceptionStrategyに任せる。
         * もし、ExceptionStrategyを変更したい場合はそちらを別のクラスに置き換える。
         * 
         * prepareExceptionViewModelで用意されたViewModelをgetResultから取得し、
         * それを、PageのViewModelに適宜追加した後、PageのViewModelをterminateし、
         * resultとしてセットする。
         * 
         * 
         * Pageモジュールが行うページブロックは、例外処理以外の部分での修飾を
         * 付け加えることにある。
         * MvcEvent::ViewModelに対して、あらかじめ、エラーのおきにくいViewModel
         * をセットする。
         * $contentに対して、ExceptionStrategyによるレンダリング結果が抽入される。
         * 
         *
         */
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this->service, 'onDispatchError'), -50); //preError
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this->service, 'onRenderError'), -50);
    }

    /**
     * Detach all our listeners from the event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    
    /**
     * Detach problem listeners specified by getListenersToDetach() and return an array of information that will
     * allow them to be reattached.
     *
     * this is from mvc controller forward plugin
     *
     * @deprecated since version number
     * @param  SharedEvents $sharedEvents Shared event manager
     * @return array
     */
    public function detachProblemListeners(MvcEvent $e)
    {
        $sharedEvents = $e->getApplication()->getEventManager()->getSharedManager();
        // Convert the problem list from two-dimensional array to more convenient id => event => class format:
        $listenersToDetach = array(array(
            'id'    => 'Zend\Stdlib\DispatchableInterface',
            'event' => MvcEvent::EVENT_DISPATCH,
            'class' => 'Zend\Mvc\View\Http\InjectViewModelListener',
        ));

        foreach ($listenersToDetach as $detach) {
            $listeners = $sharedEvents->getListeners($detach['id'], $detach['event']);
            foreach($listeners as $listener) {
                $callback = $listener->getCallback();
                if (!isset($callback[0])) {
                    continue;
                }
                if ($callback[0] instanceof $detach['class']) {
                    $sharedEvents->detach($detach['id'], $listener);
                }
            }
        }
    }
    
    public function utilizeSharedEvent(EventManagerInterface $events, $force = false)
    {
        if ($this->sharedEventAttached && (!$force)) {
            return true;
        }

        $eventDef = $this->getSharedEventDefinition();
        if ($eventDef) {
            $sharedEventManager = $events->getSharedManager();
            $eventDefDefault = ['id' => 'Zend\Stdlib\DispatchableInterface',
                         'event' => MvcEvent::EVENT_DISPATCH,
                         'callback' => function ($e) {return;},
                         'priority' => -85];
            foreach ($eventDef as $def) {
                $id = isset($def['id']) ? $def['id'] : $eventDefDefault['id'];
                $event = isset($def['event']) ? $def['event'] : $eventDefDefault['event'];
                $callback = $def['callback'] ?: $eventDefDefault['callback'];
                $priority = intval($def['priority'] ?: $eventDefDefault['priority']);
                $sharedEventManager->attach($id, $event, $callback, $priority);
            }
            $this->sharedEventAttached = true;
        }
    }

    protected function getSharedEventDefinition()
    {
        $def = array(
            array(
                'callback' => array($this->service->getResultPool(), 'fetchResultViewModel'),
                'priority' => -95,
            )
        );
        return $def;
    }
}
