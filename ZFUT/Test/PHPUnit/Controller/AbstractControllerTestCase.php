<?php

namespace ZFUT\Test\PHPUnit\Controller;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_ExpectationFailedException;
use ZFUT\Test\PHPUnit\Mvc\View\CaptureResponseListener;
use Zend\Mvc\Application;
use Zend\ModuleManager\ModuleEvent;
use Zend\Dom;

class AbstractControllerTestCase extends PHPUnit_Framework_TestCase
{
    private $application;
    private $applicationConfig;

    protected $useConsoleRequest = false;

    public function setUseConsoleRequest($boolean)
    {
        $this->useConsoleRequest = (boolean)$boolean;
    }

    public function setApplicationConfig($applicationConfig)
    {
        $this->applicationConfig = $applicationConfig;
    }

    public function getApplication()
    {
        if(null === $this->application) {
            $appConfig = $this->applicationConfig;
            if(!$this->useConsoleRequest) {
                $consoleServiceConfig = array(
                    'service_manager' => array(
                        'factories' => array(
                            'ServiceListener' => 'ZFUT\Test\PHPUnit\Mvc\Service\ServiceListenerFactory',
                        ),
                    ),
                );
                $appConfig = array_replace_recursive($appConfig, $consoleServiceConfig);
            }
            $this->application = Application::init($appConfig);
            $events = $this->application->getEventManager();
            $events->attach(new CaptureResponseListener);
        }
        return $this->application;
    }

    public function dispatch($url)
    {
        $request = $this->getApplication()->getRequest();
        if($this->useConsoleRequest) {
            $params = preg_split('#\s+#', $url);
            $request->params()->exchangeArray($params);
        } else {
            $request->setUri('http://localhost' . $url);
            $request->setBaseUrl('');
        }
        $this->getApplication()->run();
    }
    
    public function getApplicationServiceLocator()
    {
        return $this->getApplication()->getServiceManager();
    }

    public function assertResponseStatusCode($code)
    {
        $message = '';
        $response = $this->getApplication()->getResponse();
        if($this->useConsoleRequest) {
            if(!in_array($code, array(0, 1))) {
                throw new PHPUnit_Framework_ExpectationFailedException(
                    'Console status code assert value must be O (valid) or 1 (error)'
                );
            }
            $match = $response->getErrorLevel();
            if(null === $match) {
                $match = 0;
            }
            if($match != 0) {
                $message = $response->getContent();
            }
        } else {
            $match = $response->getStatusCode();
        }
        if($code != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response code "%s"' . ($message ? '. Assertion error : %s' : '%s'),
                $code, $message
            ));
        }
        $this->assertEquals($code, $match);
    }
    
    public function assertActionName($action)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $match = $routeMatch->getParam('action');
        if($action != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting action name "%s"', $action
            ));
        }
        $this->assertEquals($action, $match);
    }
    
    public function assertControllerName($controller)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $match = $routeMatch->getParam('controller');
        if($controller != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting controller name "%s"', $controller
            ));
        }
        $this->assertEquals($controller, $match);
    }
    
    public function assertRouteMatchName($route)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $match = $routeMatch->getMatchedRouteName();
        if($route != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting route matched name "%s"', $route
            ));
        }
        $this->assertEquals($route, $match);
    }
    
    protected function query($path)
    {
        $response = $this->getApplication()->getResponse();
        $dom = new Dom\Query($response->getContent());
        $result = $dom->execute($path);
        return count($result);
    }
    
    public function assertQuery($path)
    {
        $match = $this->query($path);
        if(!$match > 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS', $path
            ));
        }
        $this->assertEquals(true, $match > 0);
    }
    
    public function assertNotQuery($path)
    {
        $match = $this->query($path);
        if($match != 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s DOES NOT EXIST', $path
            ));
        }
        $this->assertEquals(0, $match);
    }
    
    public function assertQueryCount($path, $count)
    {
        $match = $this->query($path);
        if($match != $count) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s OCCURS EXACTLY %d times',
                $path, $count
            ));
        }
        $this->assertEquals($match, $count);
    }
}
