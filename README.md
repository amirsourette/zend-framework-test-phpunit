Zend Framework 2 Test with PHPUnit
==============

Version 1.0.0 Created by [Vincent Blanchon](http://developpeur-zend-framework.fr/)

Introduction
------------

ZFUT provide a library to use PHPUnit with your controllers and modules.

Use case with http request :

```php
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{    
    public function setUp()
    {
        $this->setApplicationConfig(
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
        $this->assertMatchedRouteName('home');
        $this->assertQuery('div[class="container"]');
        $this->assertNotQuery('#form');
        $this->assertQueryCount('div[class="container"]', 2);
        
        // custom assert
        $sm = $this->getApplicationServiceLocator();
        // ... here my asserts ...
    }
}
```

Use case with console request :

```php
use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class CrawlControllerTest extends AbstractConsoleControllerTestCase
{    
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../config/application.config.php'
        );
        parent::setUp();
    }
    
    public function testCrawlTweet()
    {
        // dispatch url
        $this->dispatch('--crawl-tweet');
        
        // basic assertions
        $this->assertResponseStatusCode(0);
        $this->assertActionName('tweet');
        $this->assertControllerName('cron-crawl');
        
        // custom assert
        $sm = $this->getApplicationServiceLocator();
        // ... here my asserts ...
    }
}
```
