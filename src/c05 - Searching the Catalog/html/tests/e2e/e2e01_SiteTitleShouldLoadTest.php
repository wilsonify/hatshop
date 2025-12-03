<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SiteTitleShouldLoadTest extends TestCase
{
    protected $driver;

    // Set up the WebDriver session
    protected function setUp(): void
    {
        $host = 'http://localhost:4444/';
        $capabilities = DesiredCapabilities::chrome();

        // Start the WebDriver session
        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    // Test to navigate to the site and click on the image
    public function testClickOnImage()
    {
        // Navigate to the site
        $this->driver->get('https://www.renewed-renaissance.com');

        // Find and click on the image with the src="images/title.png"
        $image = $this->driver->findElement(WebDriverBy::xpath("//img[@src='images/title.png']"));
        $image->click();

        // Wait for the page to load and check the title
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleIs("HatShop : Demo Site for Beginning PHP and PosgreSQL E-Commerce")
        );

        // Assert that the title is correct after the click
        $this->assertEquals(
            "HatShop : Demo Site for Beginning PHP and PosgreSQL E-Commerce",
            $this->driver->getTitle(),
            "Page title did not match expected value"
        );
    }

    // Tear down the WebDriver session
    protected function tearDown(): void
    {
        // Close the WebDriver session
        $this->driver->quit();
    }
}
