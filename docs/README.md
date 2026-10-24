# HatShop Documentation

This directory contains the Hugo-based documentation site for HatShop.

## Local Development

### Prerequisites

- [Hugo Extended](https://gohugo.io/installation/) (v0.128.0 or later)

### Setup

```bash
# Fetch the pinned hugo-book theme (same commit as the Pages workflow)
cd docs
HUGO_BOOK_COMMIT=0aacad68fb8e7f39835370cca8ffc7db43c26795
mkdir -p themes/hugo-book
git -C themes/hugo-book init --quiet
git -C themes/hugo-book remote add origin https://github.com/alex-shpak/hugo-book.git
git -C themes/hugo-book fetch --quiet --depth 1 origin "$HUGO_BOOK_COMMIT"
git -C themes/hugo-book checkout --quiet --detach FETCH_HEAD
test "$(git -C themes/hugo-book rev-parse HEAD)" = "$HUGO_BOOK_COMMIT"
rm -rf themes/hugo-book/.git

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

This site uses the [Hugo Book](https://github.com/alex-shpak/hugo-book) theme,
pinned to commit `0aacad68fb8e7f39835370cca8ffc7db43c26795`. The Pages
workflow and the local instructions fetch that exact commit and verify it
before building. The theme is MIT-licensed; its `LICENSE` file is included in
the fetched `themes/hugo-book/` directory. Update the commit in both places
together.

## Recent updates (developer notes)

- 2025-12-07: Integrated chapter features through C08 (Shopping Cart). The shopping cart business logic now exists in the repository and is feature-flag guarded. See the developer guide for implementation details and how to enable the cart in your environment.
- All newly introduced C08 code was scanned and fixed for SonarQube critical issues; code quality checks were run and no new CRITICAL/BLOCKER issues remain from that integration.

For developer-facing documentation about the Shopping Cart implementation, see `docs/content/docs/developers/shopping-cart.md`.
