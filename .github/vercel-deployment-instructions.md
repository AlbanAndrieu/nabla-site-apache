# Vercel Deployment Instructions

This document provides comprehensive guidelines for deploying the Nabla site to Vercel, including both manual deployments and automated CI/CD workflows.

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Initial Setup](#initial-setup)
4. [Deployment Strategies](#deployment-strategies)
5. [Environment Configuration](#environment-configuration)
6. [Monorepo Deployments](#monorepo-deployments)
7. [CI/CD Integration](#cicd-integration)
8. [Troubleshooting](#troubleshooting)
9. [Best Practices](#best-practices)

## Overview

The Nabla site is deployed to Vercel with the following architecture:
- **Root Project**: PHP API + Static HTML site (Hugo-generated or static)
- **my-app**: Next.js application (separate deployment)
- **vue-client**: Vue/Vite application (separate deployment)

## Prerequisites

### Required Tools
```bash
# Install Node.js (v20 or later recommended)
node --version

# Install Vercel CLI globally
npm install -g vercel@latest

# Verify installation
vercel --version
```

### Required Accounts
- Vercel account (https://vercel.com)
- GitHub repository access
- Appropriate permissions to create Vercel tokens

## Initial Setup

### 1. Link Your Project

First time setup requires linking your local project to Vercel:

```bash
# Navigate to project root
cd /path/to/nabla-site-apache

# Link to Vercel project
vercel link
```

Follow the prompts:
- Set up and deploy: **Yes**
- Which scope: Select your team/personal account
- Link to existing project: **Yes** (if project exists) or **No** (to create new)
- What's your project's name: `nabla-site-apache`
- In which directory is your code located: `./`

This creates a `.vercel` directory with:
```json
{
  "orgId": "team_xxxxxxxxxxxxx",
  "projectId": "prj_xxxxxxxxxxxxx"
}
```

⚠️ **Important**: The `.vercel` directory is in `.gitignore` and should never be committed.

### 2. Configure Vercel Project Settings

Access your project settings at: `https://vercel.com/[your-account]/[project-name]/settings`

#### General Settings
- **Build & Development Settings**:
  - Build Command: Leave empty (using prebuilt Hugo artifacts)
  - Output Directory: `public`
  - Install Command: `npm install`

#### Root Directory
- For root project: `.` (root)
- For my-app: `my-app`
- For vue-client: `vue-client`

### 3. Obtain Required Credentials

For GitHub Actions integration, you need three values:

#### VERCEL_TOKEN
1. Go to https://vercel.com/account/tokens
2. Click "Create Token"
3. Name: `GitHub Actions - Nabla Site`
4. Scope: Select your team/account
5. Expiration: Set appropriate duration (or no expiration for persistent CI/CD)
6. Copy the token immediately (shown only once)

#### VERCEL_ORG_ID and VERCEL_PROJECT_ID
```bash
# After running 'vercel link', get the IDs:
cat .vercel/project.json
```

Save these values - you'll need them for GitHub Actions secrets.

## Deployment Strategies

### Preview Deployments (Development)

Preview deployments are created for every push and pull request:

```bash
# Deploy to preview environment
vercel

# The CLI will output a preview URL like:
# https://nabla-site-apache-abc123.vercel.app
```

**Characteristics**:
- Unique URL for each deployment
- Perfect for testing changes
- Automatically created by CI/CD on PRs
- No production traffic impact
- Accessible to team members

### Production Deployments

Production deployments update your main site:

```bash
# Deploy to production
vercel --prod

# Your site will be available at configured domains:
# https://nabla.albandrieu.com
```

**Characteristics**:
- Updates production domains
- Requires explicit `--prod` flag
- Should only deploy from main/master branch
- Automatic rollback capability
- Zero-downtime deployments

### Deployment Workflow Comparison

| Aspect | Preview | Production |
|--------|---------|------------|
| Command | `vercel` | `vercel --prod` |
| Trigger | Any branch/PR | main/master branch |
| URL | Unique preview URL | Production domain |
| Purpose | Testing, review | Live site |
| Rollback | N/A (new deployment) | Previous deployment |

## Environment Configuration

### Environment Variables

Vercel supports three environment types:
- **Production**: Used for `--prod` deployments
- **Preview**: Used for preview deployments
- **Development**: Used for local development (`vercel dev`)

#### Setting Environment Variables via CLI

```bash
# Add environment variable
vercel env add [name]

# Example: Add API key for production
vercel env add API_KEY production
# Then paste the value when prompted

# List all environment variables
vercel env ls

# Remove environment variable
vercel env rm [name] [environment]
```

#### Setting Environment Variables via Dashboard

1. Go to project settings: `https://vercel.com/[account]/[project]/settings/environment-variables`
2. Click "Add"
3. Enter name and value
4. Select environments (Production, Preview, Development)
5. Click "Save"

#### Environment Variables in This Project

Current environment variables in `vercel.json`:
```json
{
  "env": {
    "NOW_PHP_FOO": "bar3"
  },
  "build": {
    "env": {
      "NOW_PHP_DEBUG": "1"
    }
  }
}
```

### Secrets Management

**Best Practices**:
- Never commit secrets to version control
- Use Vercel's encrypted environment variables
- Rotate secrets regularly
- Use different secrets for preview vs production
- Limit access to production secrets

## Monorepo Deployments

This repository contains multiple deployable projects. Each should be deployed separately.

### Root Project Deployment

```bash
# From repository root
vercel link
vercel --prod
```

**Configuration**: `vercel.json` in root
- PHP runtime for API
- Static files from `public/`
- Routes configuration for PHP API

### my-app (Next.js) Deployment

```bash
cd my-app
vercel link --cwd ./my-app
vercel --prod --cwd ./my-app
```

**Configuration**: `my-app/vercel.json`
- Next.js framework detected automatically
- Separate Vercel project
- Independent domain/subdomain

### vue-client (Vue/Vite) Deployment

```bash
cd vue-client
vercel link --cwd ./vue-client
vercel --prod --cwd ./vue-client
```

**Configuration**: `vue-client/vercel.json`
- Vite build detected automatically
- Separate Vercel project
- Independent deployment lifecycle

### Monorepo Best Practices

1. **Separate Projects**: Create separate Vercel projects for each deployable app
2. **Clear Naming**: Use descriptive project names (e.g., `nabla-site`, `nabla-app`, `nabla-vue`)
3. **Independent Versioning**: Each project can have its own deployment cadence
4. **Shared Dependencies**: Use npm workspaces if needed
5. **Root Directory**: Set the correct root directory in each project's settings

## CI/CD Integration

### GitHub Actions Workflow

The repository includes `.github/workflows/hugo-deploy.yml` for automated deployments.

#### Required GitHub Secrets

Add these secrets in: `GitHub Repository Settings → Secrets and variables → Actions`

1. **VERCEL_TOKEN**: Your Vercel authentication token
2. **VERCEL_ORG_ID**: Your Vercel organization/team ID
3. **VERCEL_PROJECT_ID**: Your Vercel project ID

See [docs/GITHUB_ACTIONS_SETUP.md](../docs/GITHUB_ACTIONS_SETUP.md) for detailed setup instructions.

#### Workflow Overview

```yaml
# .github/workflows/hugo-deploy.yml
jobs:
  build:
    # 1. Build Hugo site
    # 2. Upload artifacts
  
  deploy:
    # 1. Download artifacts
    # 2. Install Vercel CLI
    # 3. Pull Vercel config
    # 4. Deploy to production (main/master only)
```

#### Prebuilt Deployment Strategy

This project uses a **prebuilt deployment** strategy:

1. **Build Phase**: Hugo builds the site in GitHub Actions
2. **Artifact Upload**: Built files are uploaded as artifacts
3. **Deploy Phase**: Vercel deploys the prebuilt files using `--prebuilt` flag

**Advantages**:
- Faster deployments (no build on Vercel)
- Build happens in controlled CI environment
- Consistent builds across environments
- Better caching with GitHub Actions
- Detailed build logs in GitHub

**Implementation**:
```bash
# Build locally or in CI
hugo --minify

# Deploy prebuilt files
vercel --prebuilt --prod --token=$VERCEL_TOKEN
```

### Triggering Deployments

#### Automatic Triggers
- **Push to any branch**: Creates preview deployment
- **Pull request**: Creates preview deployment with comment
- **Push to main/master**: Creates production deployment

#### Manual Triggers
```bash
# Trigger workflow manually via GitHub UI
# Go to: Actions → Hugo Build and Deploy → Run workflow

# Or use GitHub CLI
gh workflow run hugo-deploy.yml
```

### Deployment Status

Monitor deployments:
1. **GitHub Actions**: Check workflow runs in Actions tab
2. **Vercel Dashboard**: View deployment status at https://vercel.com/dashboard
3. **Vercel CLI**: `vercel ls` shows recent deployments

## Troubleshooting

### Common Issues and Solutions

#### Issue: "Failed to deploy: Invalid token"

**Cause**: VERCEL_TOKEN is incorrect or expired

**Solution**:
1. Generate new token at https://vercel.com/account/tokens
2. Update GitHub secret: Settings → Secrets → VERCEL_TOKEN
3. Re-run workflow

#### Issue: "Project not found"

**Cause**: VERCEL_PROJECT_ID or VERCEL_ORG_ID is incorrect

**Solution**:
1. Run `vercel link` locally
2. Check `.vercel/project.json` for correct IDs
3. Update GitHub secrets with correct values

#### Issue: "Build failed" in GitHub Actions

**Cause**: Hugo build errors or missing dependencies

**Solution**:
1. Check Hugo version: Ensure using compatible Hugo version
2. Review build logs in GitHub Actions
3. Test locally: `hugo --minify`
4. Check for missing content or broken templates

#### Issue: "404 Not Found" after deployment

**Cause**: Output directory misconfiguration

**Solution**:
1. Verify `vercel.json`: `"outputDirectory": "public"`
2. Check Hugo config: `publishDir = 'public'`
3. Ensure Hugo build creates files in `public/` directory
4. Verify routes configuration in `vercel.json`

#### Issue: "Function execution timed out"

**Cause**: PHP serverless function exceeds time limit

**Solution**:
1. Optimize PHP code performance
2. Consider increasing timeout (paid plans)
3. Review function logs in Vercel dashboard
4. Check for infinite loops or slow database queries

#### Issue: Preview deployments not created for PRs

**Cause**: Vercel GitHub integration not connected

**Solution**:
1. Go to Vercel Dashboard → Project Settings → Git
2. Ensure GitHub integration is connected
3. Check GitHub permissions for Vercel app
4. Verify webhook is active in GitHub repository settings

### Debug Mode

Enable debug output for troubleshooting:

```bash
# Local debugging
DEBUG=* vercel

# GitHub Actions debugging
# Add to workflow env:
env:
  NOW_PHP_DEBUG: "1"
  VERCEL_DEBUG: "1"
```

### Viewing Logs

#### Vercel Dashboard Logs
1. Go to https://vercel.com/dashboard
2. Select your project
3. Click on a deployment
4. View "Build Logs" and "Function Logs" tabs

#### GitHub Actions Logs
1. Go to repository Actions tab
2. Select workflow run
3. Expand job steps to view detailed logs

#### CLI Logs
```bash
# View recent deployments
vercel ls

# Get deployment URL logs
vercel logs [deployment-url]
```

## Best Practices

### Security

1. **Never commit credentials**:
   - Add `.vercel` to `.gitignore` ✅ (already configured)
   - Use GitHub secrets for CI/CD credentials
   - Rotate tokens regularly

2. **Use principle of least privilege**:
   - Create separate tokens for CI/CD vs manual deployments
   - Limit token scope to specific teams/projects
   - Set token expiration dates

3. **Protect sensitive routes**:
   - Use Vercel's password protection for preview deployments
   - Implement authentication for admin routes
   - Use environment variables for API keys

### Performance

1. **Optimize build times**:
   - Use `--prebuilt` flag to avoid rebuilding on Vercel
   - Cache dependencies in GitHub Actions
   - Minimize Hugo build time with targeted builds

2. **Optimize assets**:
   - Compress images before deployment
   - Use Hugo's `--minify` flag
   - Enable Vercel's automatic image optimization
   - Configure appropriate cache headers

3. **Monitor performance**:
   - Use Vercel Analytics
   - Set up Vercel Speed Insights
   - Monitor Core Web Vitals

### Deployment Workflow

1. **Branch protection**:
   - Require PR reviews before merging to main
   - Require passing CI checks
   - Enable status checks for Vercel deployments

2. **Testing strategy**:
   - Review preview deployments before merging
   - Test on actual preview URLs (not just localhost)
   - Verify mobile responsiveness on preview deployments

3. **Rollback strategy**:
   - Keep previous deployments available in Vercel
   - Document rollback procedure
   - Use Vercel's instant rollback feature when needed

4. **Deployment hygiene**:
   - Clean up old preview deployments regularly
   - Monitor deployment frequency
   - Use descriptive commit messages (aids in deployment tracking)

### Documentation

1. **Keep documentation updated**:
   - Update this file when deployment process changes
   - Document environment variables in README
   - Maintain changelog for deployment-related changes

2. **Team communication**:
   - Notify team of deployment changes
   - Share preview URLs for review
   - Document production deployment schedule

## Additional Resources

- **Vercel Documentation**: https://vercel.com/docs
- **Vercel CLI Reference**: https://vercel.com/docs/cli
- **Hugo Documentation**: https://gohugo.io/documentation/
- **GitHub Actions Documentation**: https://docs.github.com/en/actions
- **Project-specific docs**:
  - [HUGO_MIGRATION.md](../HUGO_MIGRATION.md)
  - [GITHUB_ACTIONS_SETUP.md](../docs/GITHUB_ACTIONS_SETUP.md)
  - [README.md](../README.md)

## Support

For issues specific to this project:
- **GitHub Issues**: Open an issue in this repository
- **Email**: alban.andrieu@free.fr

For Vercel platform issues:
- **Vercel Support**: https://vercel.com/support
- **Vercel Community**: https://github.com/vercel/vercel/discussions
