# Hugo Migration Quick Start Guide

This guide provides a quick overview of the Hugo migration setup for the Nabla site.

## What's Been Done

✅ **Hugo Structure Created**
- `content/` - For Markdown content files
- `layouts/` - For HTML templates with Go templating
- `static/` - For static assets
- `archetypes/` - For content templates
- `hugo.toml` - Hugo configuration

✅ **GitHub Actions Workflow**
- `.github/workflows/hugo-deploy.yml` - Automated build and deploy
- Builds Hugo site on every push/PR
- Deploys to Vercel on main/master branch pushes

✅ **Documentation**
- `HUGO_MIGRATION.md` - Detailed migration guide
- `docs/GITHUB_ACTIONS_SETUP.md` - GitHub secrets setup
- `README.md` - Updated with Hugo migration status

✅ **Sample Templates**
- Basic Hugo templates created in `layouts/`
- Sample content in `content/_index.md`

## Current Status

- **HTML Site**: Still works - existing HTML files in `public/` are unchanged
- **Hugo Ready**: Structure is prepared for gradual migration
- **Tests**: Passing - accessibility and core functionality verified

## Next Steps for Repository Owner

### 1. Configure GitHub Secrets (Required for Auto-Deploy)

Follow the guide in [`docs/GITHUB_ACTIONS_SETUP.md`](./docs/GITHUB_ACTIONS_SETUP.md) to set up:
- `VERCEL_TOKEN`
- `VERCEL_ORG_ID`
- `VERCEL_PROJECT_ID`

### 2. Test Hugo Build Locally (Optional)

```bash
# Install Hugo
brew install hugo  # macOS
# or
sudo apt-get install hugo  # Ubuntu/Debian

# Build the site
npm run hugo:build

# Run dev server
npm run hugo:dev
```

### 3. Start Content Migration (When Ready)

1. Convert one HTML page to Markdown
2. Create matching Hugo template
3. Test the output
4. Repeat for other pages

### 4. Future: Switch to Hugo

When all content is migrated:
1. Backup `vercel.json` to `vercel.legacy.json`
2. Rename `vercel.hugo.json` to `vercel.json`
3. Deploy to Vercel
4. Archive old HTML files

## Commands Reference

### NPM Scripts

```bash
npm run hugo:build    # Build Hugo site (requires Hugo installed)
npm run hugo:dev      # Run Hugo dev server
npm run hugo:clean    # Clean Hugo build artifacts
npm run start-python  # Serve current HTML site locally
npm test             # Run Playwright tests
```

### Hugo Commands

```bash
hugo                 # Build site to public/
hugo server         # Start dev server (production mode, no drafts)
hugo server -D      # Start dev server with drafts included
hugo --minify       # Build with minification
hugo new posts/my-post.md  # Create new content
```

## File Structure

```
nabla-site-apache/
├── content/              # Markdown content (Hugo)
│   ├── _index.md
│   └── README.md
├── layouts/              # HTML templates (Hugo)
│   ├── _default/
│   │   ├── baseof.html  # Base template
│   │   └── single.html  # Single page template
│   ├── partials/
│   │   ├── header.html
│   │   └── footer.html
│   └── index.html       # Homepage template
├── static/               # Static assets (Hugo)
├── public/               # Current HTML site / Hugo output
├── hugo.toml            # Hugo configuration
├── vercel.json          # Current Vercel config (PHP + HTML)
├── vercel.hugo.json     # Future Hugo Vercel config
└── .github/workflows/
    └── hugo-deploy.yml  # Hugo build & deploy workflow
```

## Support

For questions or issues:
- Review `HUGO_MIGRATION.md` for detailed information
- Check Hugo documentation: https://gohugo.io/documentation/
- Contact: alban.andrieu@free.fr

## Important Notes

- ⚠️ **Existing HTML site remains functional** - No disruption to current deployment
- ⚠️ **Hugo is optional** - Migration can be done gradually
- ⚠️ **Backward compatible** - Old HTML files can coexist with Hugo
- ⚠️ **Test before switching** - Thoroughly test Hugo output before making it live
