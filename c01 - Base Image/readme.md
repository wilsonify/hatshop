# PHP Dockerfile Explanation

This repository contains a Dockerfile for running a PHP 8.2 application in an FPM environment. 

Below is an explanation of the tools and extensions included in the Dockerfile and why they are necessary.

## Installed Packages

### 1. `unzip`
- **Purpose**: Required by Composer to extract and install dependencies packaged as `.zip` files.
- **Why Needed**: Ensures Composer can install libraries and frameworks.

### 2. `git`
- **Purpose**: Enables Composer to fetch dependencies from Git repositories.
- **Why Needed**: Many PHP libraries are distributed via platforms like GitHub and require Git for version control.

### 3. `libzip-dev`
- **Purpose**: Provides development headers for handling `.zip` files.
- **Why Needed**: Required to compile and enable the `zip` extension for PHP, which is often used in web applications.

### 4. `libpq-dev`
- **Purpose**: Development libraries for PostgreSQL.
- **Why Needed**: Enables the `pdo_pgsql` and `pgsql` PHP extensions for interacting with PostgreSQL databases.

### 5. `sendmail`
- **Purpose**: A mail transfer agent (MTA) for sending emails from the application.
- **Why Needed**: Supports PHP scripts and applications that send emails.

## Installed PHP Extensions

### 1. `zip`
- **Purpose**: Adds support for handling ZIP files within PHP.
- **Why Needed**: Commonly used for working with compressed files in PHP applications.

### 2. `pdo`
- **Purpose**: Generic database interface for PHP.
- **Why Needed**: Enables flexible database interaction.

### 3. `pdo_pgsql` and `pgsql`
- **Purpose**: Extensions for interacting with PostgreSQL databases.
- **Why Needed**: Required for applications using PostgreSQL as their database backend.

## Composer

- **Purpose**: Dependency manager for PHP.
- **Why Needed**: Used to install libraries, such as `smarty/smarty`, and manage application dependencies efficiently.

## libraries

### Smarty/Smarty

- **Purpose**: A PHP templating engine.
- **Why Needed**: Simplifies the separation of logic and presentation in PHP applications by allowing developers to use templates for generating dynamic web pages.


## CMD

The container runs PHP-FPM in the foreground with the following command:
```bash
php-fpm --nodaemonize
```

