# HatShop Dev Deployment

This deployment runs the HatShop c02 application with:
- PHP application container
- Nginx reverse proxy for path-based routing (`/dev`)
- Cloudflare tunnel for public access

## Access

- **Public URL**: https://hatshop.renewed-renaissance.com/dev
- **Local Port**: 10080

## Usage

### Start the deployment
```bash
docker compose up -d
```

### View logs
```bash
docker compose logs -f
```

### Stop the deployment
```bash
docker compose down
```

## Architecture

```
Internet
    │
    ▼
Cloudflare Tunnel (cloudflared)
    │
    ▼
Nginx (:10080) ──────► PHP App (:80)
    │                  (hatshop c02)
    │
    └── /dev path routing
```

## Configuration

- **nginx.conf**: Routes `/dev` path to the PHP application
- **docker-compose.yaml**: Orchestrates all services

## Notes

- The Cloudflare tunnel token connects to `hatshop.renewed-renaissance.com`
- Configure the Cloudflare tunnel to point to `http://nginx:80` in the Cloudflare dashboard
- The `/dev` prefix is stripped before forwarding to the PHP app
