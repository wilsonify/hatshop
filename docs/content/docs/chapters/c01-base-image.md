---
title: "c01 - Base Image"
weight: 1
---

# Chapter 01: Base Image

This chapter establishes the Docker base image used throughout the project.

## Overview

The base image provides:
- PHP 8.x runtime
- Apache or Nginx web server
- Required PHP extensions
- Base configuration

## Getting Started

```bash
cd "src/c01 - Base Image"
docker build -t hatshop-base .
```

## What's Included

### PHP Extensions

- `pdo_pgsql` - PostgreSQL database driver
- `mbstring` - Multi-byte string support
- `json` - JSON encoding/decoding
- `xml` - XML processing

### Directory Structure

```
c01 - Base Image/
├── dockerfile      # Base image definition
└── makefile        # Build commands
```

## Usage

This image is used as the `FROM` image in subsequent chapters:

```dockerfile
FROM hatshop-base:latest

COPY html/ /var/www/html/
```

## Configuration

### Apache Version

Uses Apache 2.4 with mod_php.

### Nginx Version

Alternative Nginx configuration available in `c00 - Nginx Image/`.

## Next Steps

Continue to [Chapter 02: Foundations]({{< relref "/docs/chapters/c02-foundations" >}}) to add error handling and templating.
