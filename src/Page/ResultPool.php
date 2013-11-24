<?php
namespace Page;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\View\Model\ViewModel;
/**
 * Description of ResultPool
 *
 * @author tomoaki
 */
class ResultPool {

    protected $resultViewModels = array();

    //put your code here
    public function fetchResultViewModel(MvcEvent $e)
    {
        $resultModel = $e->getResult();
        if (!$resultModel instanceof ViewModel) {
            return;
        }
        $routeMatch       = $e->getRouteMatch();
        if (! $controllerName = $routeMatch->getParam(ModuleRouteListener::ORIGINAL_CONTROLLER, false)) {
            $controllerName   = $routeMatch->getParam('controller', 'not-found');
        }

        if (!isset($this->resultViewModels[$controllerName])) {
            $this->resultViewModels[$controllerName] = array();
        }
        $this->resultViewModels[$controllerName][] = array('model' => $resultModel, 'params' => $routeMatch->getParams());
    }

    public function getResultViewModels()
    {
        return $this->resultViewModels;
    }

    public function getResultViewModel($controllerName, array $filter = null)
    {
        if (! isset($this->resultViewModels[$controllerName])) {
            return null;
        }

        $respondedArray = $this->resultViewModels[$controllerName];

        if (! count($respondedArray)) {
            return null;
        }

        if (null !== $filter) {
            foreach ($respondedArray as $resArray) {
                foreach ($filter as $key => $val) {
                    if (!isset($resArray['params'][$key]) || $resArray['params'][$key] !== $val) {
                        continue 2;
                    }
                }
                $result = $resArray;
                break;
            }
        }
        else {
            $result = array_shift($respondedArray);
        }

        if (isset($result)) {
            return $result['model'];
        }
        else {
            return null;
        }

    }
}
