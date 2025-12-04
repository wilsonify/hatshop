---
title: "Developer Guide"
weight: 2
bookToc: true
---

# Developer Guide

This guide covers setting up a development environment and understanding the HatShop architecture.

## Prerequisites

- **Docker** and **Docker Compose**
- **Git**
- **PHP 8.x** (for local development without Docker)
- **Composer** (PHP package manager)
- **PostgreSQL 15+** (for local development)

## Quick Start

```bash
# Clone the repository
git clone https://github.com/wilsonify/hatshop.git
cd hatshop

# Start a specific chapter's environment
cd "src/c03 - Creating the Product Catalog Part I"
docker-compose up -d

# View logs
docker-compose logs -f

# Stop the environment
docker-compose down
```

## Project Structure

```
hatshop/
├── src/                          # Source code by chapter
│   ├── c01 - Base Image/         # PHP base Docker image
│   ├── c02 - Laying Out.../      # Foundations
│   ├── c03 - Creating.../        # Product Catalog I
│   └── ...
├── docs/                         # This documentation (Hugo)
├── deploy/                       # Deployment configurations
├── coverage/                     # Test coverage reports
└── Database Complete/            # Full database schema
```

### Chapter Structure

Each chapter follows this structure:

```
cXX - Chapter Name/
├── docker-compose.yaml    # Container orchestration
├── dockerfile             # Application container
├── makefile               # Build commands
├── 000-default.conf       # Apache configuration
├── supervisord.conf       # Process management
├── database/              # SQL scripts
│   └── *.sql
└── html/                  # PHP application
    ├── index.php          # Entry point
    ├── include/           # Configuration
    │   ├── config.php
    │   ├── app_top.php
    │   └── app_bottom.php
    ├── business/          # Business logic
    ├── presentation/      # Smarty templates
    └── tests/             # PHPUnit tests
```

## Architecture

### MVC-like Pattern

HatShop uses a simplified MVC architecture:

- **Model** (`business/`) - Database access and business logic
- **View** (`presentation/templates/`) - Smarty templates  
- **Controller** (`index.php` + `presentation/`) - Request routing

### Key Components

#### Database Handler

```php
// business/database_handler.php
class DatabaseHandler {
    private static ?PDO $mHandler = null;
    
    public static function getHandler(): PDO {
        if (self::$mHandler === null) {
            self::$mHandler = new PDO($dsn, $user, $pass);
        }
        return self::$mHandler;
    }
}
```

#### Smarty Page Class

```php
// presentation/page.php
class Page extends Smarty {
    public function __construct() {
        parent::__construct();
        $this->setTemplateDir(TEMPLATE_DIR);
        $this->setCompileDir(COMPILE_DIR);
        // Register custom plugins
        $this->registerPlugin('function', 'load_departments_list', ...);
    }
}
```

## Development Workflow

### Running Tests

```bash
cd "src/c03 - Creating the Product Catalog Part I/html"

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit

# Run with coverage
./vendor/bin/phpunit --coverage-clover coverage.xml
```

### Code Quality

```bash
# Run SonarQube analysis (from project root)
sonar-scanner -Dsonar.token=YOUR_TOKEN
```

### Adding Features

1. Create a new chapter folder or modify existing
2. Update database schema if needed (`database/`)
3. Add business logic (`business/`)
4. Create templates (`presentation/templates/`)
5. Register Smarty plugins if needed
6. Write tests (`tests/`)
7. Update documentation

## Database

### Connection

Configure in `include/config.php`:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'hatshop');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'hatshop');
define('DB_DATABASE', getenv('DB_DATABASE') ?: 'hatshop');
```

### Schema

Key tables:
- `department` - Product departments
- `category` - Product categories  
- `product` - Products
- `shopping_cart` - Cart items
- `orders` - Customer orders
- `customer` - Customer accounts

## Smarty Templating

### Custom Plugins

Located in `presentation/smarty_plugins/`:

```php
// function.load_departments_list.php
function smarty_function_load_departments_list($params, $template) {
    $template->assign($params['assign'], Catalog::getDepartments());
}
```

### Template Example

```smarty
{load_departments_list assign="departments"}
{foreach $departments as $dept}
    <a href="{$dept.link|prepare_link}">{$dept.name}</a>
{/foreach}
```

## Debugging

### Error Handler

Errors are captured by `business/error_handler.php` and displayed with stack traces in development mode.

### Logs

```bash
# View Docker logs
docker-compose logs -f app

# Apache logs (inside container)
tail -f /var/log/apache2/error.log
```

## Deployment Environments

HatShop uses a three-environment deployment model with a promotion workflow.

### Environment Overview

| Environment | Technology | Port | URL Path | Purpose |
|-------------|------------|------|----------|---------|
| Dev | Docker Compose | 10080 | `/dev` | Local development and testing |
| Stage | KIND (Kubernetes) | 10081 | `/stage` | Pre-production validation |
| Prod | KIND (Kubernetes) | 10082 | `/prod` | Production |

### Directory Structure

```
deploy/
├── 01_dev/
│   ├── hatshop/
│   │   ├── .env              # Source of truth for secrets
│   │   ├── docker-compose.yaml
│   │   ├── Makefile          # Includes promote-* targets
│   │   └── nginx.conf
│   └── cloudflare/
│       └── config.yaml
├── 02_stage/
│   ├── .env                  # Promoted from dev
│   ├── kind-config.yaml
│   ├── Makefile
│   └── base/                 # Kustomize manifests
└── 03_prod/
    └── (similar to stage)
```

### Promotion Workflow

Changes flow from dev to stage to prod. Environment secrets are explicitly promoted to ensure only tested configurations reach higher environments.

```
┌─────────┐    promote-stage    ┌─────────┐    promote-prod    ┌─────────┐
│   Dev   │ ─────────────────►  │  Stage  │ ─────────────────► │  Prod   │
└─────────┘                     └─────────┘                    └─────────┘
Docker Compose                  KIND Cluster                   KIND Cluster
Port 10080                      Port 10081                     Port 10082
```

### Working with Dev Environment

```bash
cd deploy/01_dev/hatshop

# Start the environment
make up

# View logs
make logs

# Check status
make status

# Stop the environment
make down
```

### Promoting to Stage

After testing in dev, promote the configuration to stage:

```bash
# From dev directory
cd deploy/01_dev/hatshop

# Promote .env to stage
make promote-stage

# Then deploy stage
cd ../../02_stage
make all
```

### Promoting to Prod

After validation in stage:

```bash
# From dev directory
cd deploy/01_dev/hatshop

# Promote .env to prod
make promote-prod

# Then deploy prod
cd ../../03_prod
make all
```

### Testing Your Changes

1. **Local development**: Use the chapter-specific `docker-compose.yaml` in `src/`
2. **Integration testing**: Deploy to dev environment
3. **Pre-production validation**: Promote to stage
4. **Release**: Promote to prod

## Next Steps

- Read the [Chapter Guides]({{< relref "/docs/chapters" >}}) to understand each feature
- Check the [Admin Guide]({{< relref "/docs/admins" >}}) for deployment
