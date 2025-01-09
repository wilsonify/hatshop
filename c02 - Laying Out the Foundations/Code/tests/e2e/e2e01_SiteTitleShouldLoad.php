<?php

namespace Facebook\WebDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

require_once('vendor/autoload.php');

// Selenium server host and port
$host = 'http://localhost:4444/';

// Set up Chrome capabilities
$capabilities = DesiredCapabilities::chrome();

// Start the WebDriver session
$driver = RemoteWebDriver::create($host, $capabilities);

// Navigate to the site
$driver->get('https://www.renewed-renaissance.com');

// Click on the image with the src="images/title.png"
$image = $driver->findElement(WebDriverBy::xpath("//img[@src='images/title.png']"));
$image->click();

// Wait for the page to load without error (we'll just check the title as an indicator)
$driver->wait(10, 500)->until( WebDriverExpectedCondition::titleIs("HatShop : Demo Site for Beginning PHP and PosgreSQL E-Commerce")  );

// Output the result
echo "The title is: " . $driver->getTitle() . "\n";

// Close the WebDriver session
$driver->quit();

?>
