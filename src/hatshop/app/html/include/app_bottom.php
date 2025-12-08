<?php
/**
 * HatShop Application Cleanup.
 *
 * This file performs cleanup tasks at the end of request processing.
 */

use Hatshop\Core\DatabaseHandler;

// Close database connection
DatabaseHandler::close();
