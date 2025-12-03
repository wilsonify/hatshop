---
title: "HatShop E-Commerce Platform"
type: docs
---

# HatShop E-Commerce Platform

Welcome to the HatShop documentation! This project is a comprehensive PHP and PostgreSQL e-commerce application based on the book *Beginning PHP and PostgreSQL E-Commerce: From Novice to Professional*.

{{< columns >}}

- ## 🛒 For Users

  Learn how to browse products, manage your shopping cart, and complete purchases.

  [Get Started →]({{< relref "/docs/users" >}})

- ## 👨‍💻 For Developers

  Set up your development environment and understand the codebase architecture.

  [Developer Guide →]({{< relref "/docs/developers" >}})

- ## ⚙️ For Admins

  Deploy, configure, and maintain your HatShop installation.

  [Admin Guide →]({{< relref "/docs/admins" >}})

{{< /columns >}}

---

## Quick Start

```bash
# Clone the repository
git clone https://github.com/wilsonify/hatshop.git
cd hatshop

# Start with Docker Compose (any chapter)
cd "src/c03 - Creating the Product Catalog Part I"
docker-compose up -d
```

Then open [http://localhost:8080](http://localhost:8080) in your browser.

---

## Project Structure

The project is organized by chapters, each adding new features:

| Chapter | Feature | Description |
|---------|---------|-------------|
| c01 | Base Image | PHP 8.x with Apache/Nginx base |
| c02 | Foundations | Error handling, Smarty templating |
| c03 | Product Catalog I | Departments, categories, products |
| c04 | Product Catalog II | Product details, thumbnails |
| c05 | Search | Full-text product search |
| c06 | PayPal | Payment integration |
| c07 | Admin | Catalog administration |
| c08 | Shopping Cart | Cart functionality |
| c09 | Orders | Order management |
| c10 | Recommendations | Product recommendations |
| c11 | Customers | Customer accounts |
| c12 | Order Storage | Persistent orders |
| c13-14 | Order Pipeline | Order processing workflow |
| c15 | Credit Cards | Payment processing |
| c16 | Reviews | Product reviews |
| c17 | Web Services | API integrations |
| c18 | Kubernetes | Container orchestration |
| c19 | Identity | Authentication provider |
| c20 | OAuth2 | OAuth2 proxy |
| c21 | Selenium | E2E testing |
| c22 | Zero Trust | Security hardening |

---

## Technology Stack

- **Backend**: PHP 8.x
- **Database**: PostgreSQL
- **Templating**: Smarty 5.x
- **Web Server**: Apache 2.x / Nginx
- **Containerization**: Docker & Docker Compose
- **Orchestration**: Kubernetes (KIND)
- **Testing**: PHPUnit, Selenium
- **CI/CD**: GitHub Actions
- **Code Quality**: SonarQube
