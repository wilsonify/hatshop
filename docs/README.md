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
