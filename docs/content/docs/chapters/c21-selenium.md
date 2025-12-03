---
title: "c21 - Selenium Testing"
weight: 21
---

# Chapter 21: Selenium E2E Testing

Implement end-to-end testing with Selenium WebDriver.

## Overview

- **Selenium Grid** - Browser automation infrastructure
- **WebDriver** - Browser control API
- **Test Suites** - Automated user journeys

## Getting Started

```bash
cd "c21 - Selenium"
docker-compose up -d
```

## Selenium Grid Setup

```yaml
services:
  selenium-hub:
    image: selenium/hub
    ports:
      - 4442:4442
      - 4443:4443
      - 4444:4444
      
  chrome:
    image: selenium/node-chrome
    depends_on:
      - selenium-hub
    environment:
      - SE_EVENT_BUS_HOST=selenium-hub
```

## Test Example (PHP)

```php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class CheckoutTest extends TestCase {
    private $driver;
    
    public function setUp(): void {
        $this->driver = RemoteWebDriver::create(
            'http://selenium-hub:4444/wd/hub',
            DesiredCapabilities::chrome()
        );
    }
    
    public function testAddToCart(): void {
        $this->driver->get('http://hatshop/');
        
        // Navigate to product
        $this->driver->findElement(WebDriverBy::linkText('Regional'))
            ->click();
        
        // Add to cart
        $this->driver->findElement(WebDriverBy::className('add-to-cart'))
            ->click();
        
        // Verify cart
        $cartCount = $this->driver->findElement(WebDriverBy::id('cart-count'))
            ->getText();
        $this->assertEquals('1', $cartCount);
    }
}
```

## Running Tests

```bash
# Run all E2E tests
./vendor/bin/phpunit tests/e2e/

# Run specific test
./vendor/bin/phpunit tests/e2e/CheckoutTest.php
```

## Next Steps

Continue to [Chapter 22: Zero Trust Security]({{< relref "/docs/chapters/c22-zero-trust" >}}).
