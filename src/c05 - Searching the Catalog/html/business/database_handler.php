<?php

namespace Hatshop\Business;

use PDO;

// Class providing generic data access functionality
class DatabaseHandler
{
  // Hold an instance of the PDO class
  private static $handler;


  private function __construct()
  {
    // Private constructor to prevent direct creation of object
  }

  // Return an initialized database handler
  private static function getHandler()
  {
    // Create a database connection only if one doesn't already exist
    if (!isset(self::$handler))
    {
      // Execute code catching potential exceptions
      try
      {
        // Create a new PDO class instance
        self::$handler =
          new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD,
                  array(PDO::ATTR_PERSISTENT => DB_PERSISTENCY));

        // Configure PDO to throw exceptions
        self::$handler->setAttribute(PDO::ATTR_ERRMODE,
                                       PDO::ERRMODE_EXCEPTION);
      }
      catch (PDOException $e)
      {
        // Close the database handler and trigger an error
        self::close();
        trigger_error($e->getMessage(), E_USER_ERROR);
      }
    }

    // Return the database handler
    return self::$handler;
  }

  // Clear the PDO class instance
  public static function close()
  {
    self::$handler = null;
  }

  // Wrapper method for PDO::prepare
  public static function prepare($queryString)
  {
    // Execute code catching potential exceptions
    try
    {
      // Get the database handler and prepare the query
      $database_handler = self::getHandler();

      // Return the prepared statement
      return $database_handler->prepare($queryString);
    }
    catch (PDOException $e)
    {
      // Close the database handler and trigger an error
      self::close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }
  }

  // Wrapper method for PDOStatement::execute
  public static function execute($statementHandler, $params = null)
  {
    try
    {
      // Try to execute the query
      $statementHandler->execute($params);
    }
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }
  }

  // Wrapper method for PDOStatement::fetchAll
  public static function getAll($statementHandler, $params = null,
                                $fetchStyle = PDO::FETCH_ASSOC)
  {
    // Initialize the return value to null
    $result = null;

    // Try executing the prepared statement received as parameter
    try
    {
      self::execute($statementHandler, $params);

      $result = $statementHandler->fetchAll($fetchStyle);
    }
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }

    // Return the query results
    return $result;
  }

  // Wrapper method for PDOStatement::fetch
  public static function getRow($statementHandler, $params = null,
                                $fetchStyle = PDO::FETCH_ASSOC)
  {
    // Initialize the return value to null
    $result = null;

    // Try executing the prepared statement received as parameter
    try
    {
      self::execute($statementHandler, $params);

      $result = $statementHandler->fetch($fetchStyle);
    }
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }

    // Return the query results
    return $result;
  }

  // Return the first column value from a row
  public static function getOne($statementHandler, $params = null)
  {
    // Initialize the return value to null
    $result = null;

    // Try executing the prepared statement received as parameter
    try
    {
      /* Execute the query, and save the first value of the result set
         (first column of the first row) to $result */
      self::execute($statementHandler, $params);

      $result = $statementHandler->fetch(PDO::FETCH_NUM);
      $result = $result[0];
    }
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }

    // Return the query results
    return $result;
  }
}

