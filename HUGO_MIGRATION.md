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

## GitHub Actions and Deployment

### Setting up GitHub Actions

The repository includes a GitHub Actions workflow (`.github/workflows/hugo-deploy.yml`) that automatically:
1. Builds the Hugo site on every push and pull request
2. Deploys to Vercel production on pushes to main/master branch

**Required Setup**: Before the workflow can deploy, you need to configure GitHub secrets. See [GitHub Actions Setup Guide](./docs/GITHUB_ACTIONS_SETUP.md) for detailed instructions.

### Local Development vs Production

- **Local Development**: HTML files in `public/` are served directly
- **Production (after Hugo migration)**: Hugo builds the site from `content/` and `layouts/` into `public/`

### Vercel Configuration

Two Vercel configurations are available:

1. **vercel.json** - Current configuration for PHP + static HTML
2. **vercel.hugo.json** - Future configuration for Hugo deployment

To switch to Hugo deployment on Vercel, rename `vercel.hugo.json` to `vercel.json` (backup the original first).

## Next Steps for Complete Migration

1. ✅ **Phase 1 Complete**: Hugo structure and GitHub Actions are set up
2. **Phase 2**: Create Hugo layouts that match the existing HTML design
3. **Phase 3**: Convert HTML content to Markdown in `content/` directory
4. **Phase 4**: Test Hugo build output matches existing site
5. **Phase 5**: Switch Vercel to use Hugo configuration
6. **Phase 6**: Remove old HTML files and serve Hugo-generated content

## Benefits of Hugo Migration

- **Content Management**: Write content in Markdown instead of HTML
- **Templating**: Reusable components via Hugo templates
- **Performance**: Fast build times and optimized output
- **SEO**: Better meta tag management and sitemap generation
- **Maintainability**: Separation of content and presentation
- **Version Control**: Easier to review content changes in Markdown
