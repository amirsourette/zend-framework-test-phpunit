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
    protected $application;
    protected static $useConsoleRequest = false;
    private static $applicationConfig;

    public function setUseConsoleRequest($boolean)
    {
        self::$useConsoleRequest = (boolean)$boolean;
    }

    public static function setApplicationConfig($applicationConfig)
    {
        if(!self::$useConsoleRequest) {
            $consoleServiceConfig = array(
                'service_manager' => array(
                    'factories' => array(
                        'ServiceListener' => 'ZFUT\Test\PHPUnit\Mvc\Service\ServiceListenerFactory',
                    ),
                ),
            );
            $applicationConfig = array_replace_recursive($applicationConfig, $consoleServiceConfig);
        }
        self::$applicationConfig = $applicationConfig;
    }

    public function setUp()
    {
        $this->application = Application::init(self::$applicationConfig);
        $events = $this->application->getEventManager();
        $events->attach(new CaptureResponseListener);
        parent::setUp();
    }

    public function dispatch($url)
    {
        $request = $this->application->getRequest();
        $request->setUri('http://localhost' . $url);
        $request->setBaseUrl('');
        $this->application->run();
    }
    
    public function getApplicationServiceLocator()
    {
        return $this->application->getServiceManager();
    }

    public function assertResponseStatusCode($code)
    {
        $response = $this->application->getResponse();
        $match = $response->getStatusCode();
        if($code != $response->getStatusCode()) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf('Failed asserting response code "%s"', $code));
        }
        $this->assertEquals($code, $match);
    }
    
    public function assertActionName($action)
    {
        $routeMatch = $this->application->getMvcEvent()->getRouteMatch();
        $match = $routeMatch->getParam('action');
        if($action != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf('Failed asserting action name "%s"', $action));
        }
        $this->assertEquals($action, $match);
    }
    
    public function assertControllerName($controller)
    {
        $routeMatch = $this->application->getMvcEvent()->getRouteMatch();
        $match = $routeMatch->getParam('controller');
        if($controller != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf('Failed asserting controller name "%s"', $controller));
        }
        $this->assertEquals($controller, $match);
    }
    
    public function assertRouteMatchName($route)
    {
        $routeMatch = $this->application->getMvcEvent()->getRouteMatch();
        $match = $routeMatch->getMatchedRouteName();
        if($route != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf('Failed asserting route matched name "%s"', $route));
        }
        $this->assertEquals($route, $match);
    }
    
    protected function query($path)
    {
        $response = $this->application->getResponse();
        $dom = new Dom\Query($response->getContent());
        $result = $dom->execute($path);
        return count($result);
    }
    
    public function assertQuery($path)
    {
        $match = $this->query($path);
        if(!$match > 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf('Failed asserting node DENOTED BY %s EXISTS', $path));
        }
        $this->assertEquals(true, $match > 0);
    }
    
    public function assertNotQuery($path)
    {
        $match = $this->query($path);
        if($match != 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf('Failed asserting node DENOTED BY %s DOES NOT EXIST', $path));
        }
        $this->assertEquals(0, $match);
    }
    
    public function assertQueryCount($path, $count)
    {
        $match = $this->query($path);
        if($match != $count) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf('Failed asserting node DENOTED BY %s OCCURS EXACTLY %d times', $path, $count));
        }
        $this->assertEquals($match, $count);
    }
}
