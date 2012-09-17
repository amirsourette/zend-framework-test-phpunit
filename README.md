Zend Framework 2 Test with PHPUnit
==============

Version 0.1.0 Created by [Vincent Blanchon](http://developpeur-zend-framework.fr/)

Introduction
------------

ZFUT provide a library to use PHPUnit with your controllers and modules.

Use case :

```php
<?php

namespace ModuleTest\Application\Controller;

use ZFUT\Test\PHPUnit\Controller\AbstractControllerTestCase;

class IndexControllerTest extends AbstractControllerTestCase
{    
    public function setUp()
    {
        AbstractControllerTestCase::setApplicationConfig(
            include __DIR__ . '/../../../config/application.config.php'
        );
        parent::setUp();
    }
    
    public function testCanDisplayIndex()
    {
        // dispatch url
        $this->dispatch('/');
        
        // basic assertions
        $this->assertResponseStatusCode(200);
        $this->assertActionName('index');
        $this->assertControllerName('application-index');
        $this->assertRouteMatchName('home');
        $this->assertQuery('div[class="container"]');
        $this->assertQueryCount('div[class="container"]', 2);
        
        // custom assert
        $sm = $this->getApplicationServiceLocator();
        // ... here my asserts ...
    }
}
```
