# Hugo Migration Setup

This directory structure is prepared for migrating the static HTML site to Hugo.

## Directory Structure

- **content/**: Markdown content files (will replace HTML files gradually)
- **layouts/**: Hugo template files (HTML templates with Go templating)
- **static/**: Static assets that will be copied as-is to the output
- **themes/**: Hugo themes (currently empty, can use custom or existing theme)
- **archetypes/**: Content templates for new pages
- **data/**: Data files (JSON, YAML, TOML) for structured content

## Current State

The existing HTML files remain in the `public/` directory and are served directly.
Hugo will be configured to build alongside these files during the migration phase.

## Migration Process

1. **Phase 1** (Current): Setup Hugo structure and configuration
2. **Phase 2**: Create Hugo layouts/templates matching existing HTML design
3. **Phase 3**: Convert HTML content to Markdown in content/ directory
4. **Phase 4**: Test Hugo build and verify output
5. **Phase 5**: Switch Vercel to use Hugo-generated output

## Running Hugo Locally

```bash
# Install Hugo (if not already installed)
# On Ubuntu/Debian:
sudo apt-get install hugo

# On macOS:
brew install hugo

# Build the site
hugo

# Run development server
hugo server -D
```

## Building for Production

```bash
hugo --minify
```

This will generate the static site in the `public/` directory.
