# PHP with Nginx in Docker

This document explains why Nginx is commonly used to run PHP in a Docker container.

## Typical Architecture

1. **Nginx**:
   - Handles HTTP requests and serves static files.
   - Proxies dynamic requests to PHP-FPM.
2. **PHP-FPM**:
   - Processes PHP scripts and sends responses back to Nginx.

## Why Use Nginx with PHP?

### 1. **Separation of Concerns**
- Nginx handles HTTP requests, static files, and SSL/TLS termination.
- PHP-FPM focuses on executing PHP application logic.
- Improves maintainability and scalability.

### 2. **Efficient Static Content Handling**
- Nginx serves static files (CSS, JS, images) directly without involving PHP.
- Reduces resource usage and enhances performance.

### 3. **Reverse Proxy Functionality**
- Nginx forwards HTTP requests to PHP-FPM (FastCGI Process Manager).
- Ensures efficient management of client connections and request routing.

### 4. **Load Balancing and Scalability**
- Nginx distributes requests across multiple PHP-FPM instances or containers.
- Facilitates scalable application architecture.

### 5. **SSL/TLS Termination**
- Nginx handles SSL/TLS connections, reducing load on the backend.
- Simplifies securing the application.


## Why Not Run PHP Directly?

While PHP includes an embedded web server (`php -S`), it is not recommended for production use because:
- It lacks performance optimizations for high traffic.
- It does not support advanced features like SSL termination or load balancing.
- It is less secure compared to using a dedicated web server.
