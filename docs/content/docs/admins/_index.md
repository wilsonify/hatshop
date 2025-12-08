---
title: "Admin Guide"
weight: 3
bookToc: true
---

# Administrator Guide

This guide covers deploying, configuring, and maintaining HatShop in production.

## Deployment Guides

- [Deploy to Dev Environment]({{< relref "deploy-dev" >}}) - Docker Compose with Cloudflare Tunnel
- [Deploy to Stage Environment]({{< relref "deploy-stage" >}}) - KIND (Kubernetes) with SOPS secrets
- [Deploy to Kubernetes]({{< relref "deploy-kubernetes" >}}) - Production Kubernetes deployment
- [Feature Flags]({{< relref "feature-flags" >}}) - Controlling features by chapter level

## Secrets Management

Stage and production environments use [SOPS](https://github.com/getsops/sops) with [age](https://github.com/FiloSottile/age) encryption:

| Environment | Secrets Storage |
|-------------|-----------------|
| Dev | `.env.enc` (SOPS-encrypted dotenv) |
| Stage | `hatshop-secrets.enc.yaml` (SopsSecret CRD) |
| Prod | External secrets manager |

### Age Public Key

```
age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek
```

## Deployment Options

### Docker Compose (Dev Environment)

```bash
cd deploy/01_dev/hatshop
sops decrypt .env.enc > .env
docker-compose up -d
```

### Kubernetes with KIND (Stage Environment)

```bash
cd deploy/02_stage
make all
```

### Kubernetes (Production)

See [Deploy to Kubernetes]({{< relref "deploy-kubernetes" >}}) for production setup.

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | PostgreSQL host | `localhost` |
| `DB_USER` | Database user | `hatshop` |
| `DB_PASSWORD` | Database password | `hatshop` |
| `DB_DATABASE` | Database name | `hatshop` |
| `HATSHOP_HTTP_SERVER_HOST` | Public hostname | `localhost` |
| `HATSHOP_HTTP_SERVER_PORT` | HTTP port | `80` |
| `HATSHOP_VIRTUAL_LOCATION` | URL base path | `/` |

### Apache Configuration

The `000-default.conf` file configures:
- Document root
- Directory permissions
- PHP handler
- Error logging

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### PHP Configuration

Key `php.ini` settings:
- `display_errors = Off` (production)
- `log_errors = On`
- `error_log = /var/log/php/error.log`
- `session.cookie_secure = On` (with HTTPS)

## Database Administration

### Initial Setup

```bash
# Connect to PostgreSQL
psql -h localhost -U hatshop -d hatshop

# Run schema
\i database/hatshop.sql
```

### Backup

```bash
# Full backup
pg_dump -h localhost -U hatshop hatshop > backup.sql

# Restore
psql -h localhost -U hatshop hatshop < backup.sql
```

### Maintenance

```sql
-- Vacuum and analyze
VACUUM ANALYZE;

-- Check table sizes
SELECT relname, pg_size_pretty(pg_total_relation_size(relid))
FROM pg_catalog.pg_statio_user_tables
ORDER BY pg_total_relation_size(relid) DESC;
```

## Security

### HTTPS Setup

1. Obtain SSL certificate (Let's Encrypt recommended)
2. Configure Apache SSL module
3. Enable HSTS headers

```apache
<VirtualHost *:443>
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/hatshop.crt
    SSLCertificateKeyFile /etc/ssl/private/hatshop.key
    
    Header always set Strict-Transport-Security "max-age=31536000"
</VirtualHost>
```

### OAuth2 Proxy (Chapter 20)

For enterprise authentication:
```yaml
# docker-compose.yaml
oauth2-proxy:
  image: quay.io/oauth2-proxy/oauth2-proxy
  environment:
    - OAUTH2_PROXY_PROVIDER=oidc
    - OAUTH2_PROXY_OIDC_ISSUER_URL=...
```

### Zero Trust (Chapter 22)

Implement zero trust principles:
- Verify every request
- Least privilege access
- Network segmentation

## Monitoring

### Health Checks

```bash
# Application health
curl http://localhost/health

# Database connectivity
curl http://localhost/api/status
```

### Logging

Configure centralized logging:
```bash
# View all logs
docker-compose logs -f

# Application logs only
docker-compose logs -f app
```

### Metrics

Consider integrating:
- Prometheus for metrics collection
- Grafana for visualization
- AlertManager for notifications

## Scaling

### Horizontal Scaling

```yaml
# docker-compose.yaml
services:
  app:
    deploy:
      replicas: 3
```

### Load Balancing

Use Nginx or HAProxy as a load balancer:
```nginx
upstream hatshop {
    server app1:80;
    server app2:80;
    server app3:80;
}
```

### Database Scaling

- Read replicas for read-heavy workloads
- Connection pooling with PgBouncer
- Consider PostgreSQL streaming replication

## Troubleshooting

### Common Issues

#### Database Connection Failed

```
SQLSTATE[08006] [7] connection refused
```

**Solution**: Check PostgreSQL is running and accessible:
```bash
docker-compose ps
docker-compose logs db
```

#### Smarty Template Errors

**Solution**: Ensure compile directory is writable:
```bash
chmod 777 presentation/templates_c
```

#### Permission Denied

**Solution**: Fix file ownership:
```bash
chown -R www-data:www-data /var/www/html
```

### Debug Mode

Enable in `include/config.php`:
```php
define('IS_WARNING_FATAL', true);
define('DEBUGGING', true);
```

⚠️ **Never enable in production!**

## Maintenance Tasks

### Regular Tasks

- [ ] Database backups (daily)
- [ ] Log rotation (weekly)
- [ ] Security updates (monthly)
- [ ] SSL certificate renewal (before expiry)
- [ ] Disk space monitoring (continuous)

### Upgrade Procedure

1. Backup database and files
2. Put site in maintenance mode
3. Pull latest code
4. Run database migrations
5. Clear template cache
6. Test thoroughly
7. Remove maintenance mode
