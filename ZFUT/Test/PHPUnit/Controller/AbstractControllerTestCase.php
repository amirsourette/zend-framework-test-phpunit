<?php

namespace ZFUT\Test\PHPUnit\Controller;

use PHPUnit_Framework_TestCase;
use ZFUT\Test\PHPUnit\Mvc\View\CaptureResponseListener;
use Zend\Mvc\Application;
use Zend\ModuleManager\ModuleEvent;
use Zend\Dom;

class AbstractControllerTestCase extends PHPUnit_Framework_TestCase
{
    protected $application;
    protected static $applicationConfig;
    protected static $useConsoleRequest = false;

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
        $this->assertEquals($code, $response->getStatusCode());
    }
    
    public function assertActionName($action)
    {
        $routeMatch = $this->application->getMvcEvent()->getRouteMatch();
        $this->assertEquals($action, $routeMatch->getParam('action'));
    }
    
    public function assertControllerName($controller)
    {
        $routeMatch = $this->application->getMvcEvent()->getRouteMatch();
        $this->assertEquals($controller, $routeMatch->getParam('controller'));
    }
    
    public function assertRouteMatchName($route)
    {
        $routeMatch = $this->application->getMvcEvent()->getRouteMatch();
        $this->assertEquals($route, $routeMatch->getMatchedRouteName());
    }
}