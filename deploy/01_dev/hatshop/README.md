# HatShop Dev Deployment

This deployment runs the HatShop application with features up to **Chapter 12**:
- PHP application container
- Nginx reverse proxy for path-based routing (`/dev`)
- Cloudflare tunnel for public access

## Features Enabled (Chapter 12)

| Chapter | Feature |
|---------|---------|
| 2 | Departments |
| 3 | Categories |
| 4 | Products, Product Details, Pagination |
| 5 | Search |
| 6 | PayPal Payments |
| 7 | Catalog Administration |
| 8 | Shopping Cart |
| 9 | Customer Orders |
| 10 | Product Recommendations |
| 11 | Customer Details |
| 12 | Order Storage |

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
    │                  (hatshop)
    │
    └── /dev path routing
```

## Configuration

- **nginx.conf**: Routes `/dev` path to the PHP application
- **docker-compose.yaml**: Orchestrates all services
- **.env**: Environment variables including `HATSHOP_CHAPTER_LEVEL=12`

## Notes

- The Cloudflare tunnel token connects to `hatshop.renewed-renaissance.com`
- Configure the Cloudflare tunnel to point to `http://nginx:80` in the Cloudflare dashboard
- The `/dev` prefix is stripped before forwarding to the PHP app
