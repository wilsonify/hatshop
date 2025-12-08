# HatShop Documentation

This directory contains the Hugo-based documentation site for HatShop.

## Local Development

### Prerequisites

- [Hugo Extended](https://gohugo.io/installation/) (v0.128.0 or later)

### Setup

```bash
# Clone the hugo-book theme
cd docs
mkdir -p themes
git clone https://github.com/alex-shpak/hugo-book themes/hugo-book

# Start the development server
hugo server -D
```

Then visit [http://localhost:1313/hatshop/](http://localhost:1313/hatshop/)

### Build

```bash
hugo --gc --minify
```

Output will be in `public/`.

## Deployment

The documentation is automatically deployed to GitHub Pages when changes are pushed to the `master` branch.

**Live Site**: [https://wilsonify.github.io/hatshop/](https://wilsonify.github.io/hatshop/)

## Structure

```
docs/
├── hugo.toml              # Hugo configuration
├── content/
│   ├── _index.md          # Homepage
│   └── docs/
│       ├── _index.md      # Docs landing
│       ├── users/         # User guide
│       ├── developers/    # Developer guide
│       ├── admins/        # Admin guide
│       └── chapters/      # Chapter-by-chapter docs
└── .gitignore
```

## Theme

This site uses the [Hugo Book](https://github.com/alex-shpak/hugo-book) theme.

## Recent updates (developer notes)

- 2025-12-07: Integrated chapter features through C08 (Shopping Cart). The shopping cart business logic now exists in the repository and is feature-flag guarded. See the developer guide for implementation details and how to enable the cart in your environment.
- All newly introduced C08 code was scanned and fixed for SonarQube critical issues; code quality checks were run and no new CRITICAL/BLOCKER issues remain from that integration.

For developer-facing documentation about the Shopping Cart implementation, see `docs/content/docs/developers/shopping-cart.md`.
