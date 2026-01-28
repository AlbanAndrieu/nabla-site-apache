## <!-- markdown-link-check-disable-next-line -->

## layout: default

Text can be **bold**, _italic_, or ~~strikethrough~~.

# [![Nabla](https://nabla.albandrieu.com/assets/nabla/nabla-4.png)](https://github.com/AlbanAndrieu/nabla-site-apache) nabla-site-apache

[![License: APACHE](http://img.shields.io/:license-apache-blue.svg?style=flat-square)](http://www.apache.org/licenses/LICENSE-2.0.html)

[CHANGELOG](./CHANGELOG.html).

## Project Goal

This is a Hugo-powered website for Nabla company that promotes Alban Andrieu as an experienced DevSecOps professional.

The site is built with [Hugo](https://gohugo.io/), a fast and modern static site generator.

## Monorepo Structure

This repository contains multiple deployable projects:

1. **Root Project** - Hugo static site + PHP API
2. **my-app/** - Next.js application (separate Vercel deployment)
3. **vue-client/** - Vue/Vite application (separate Vercel deployment)

## Development

### Building the Hugo Site

```bash
# Install Hugo (if not already installed)
# See https://gohugo.io/installation/

# Build the site
hugo

# Build with minification (production)
hugo --minify

# Start development server with live reload
hugo server

# Start dev server with drafts
hugo server -D
```

### Local Development

```bash
# Start Hugo development server (recommended)
hugo server

# Or use Python simple server (serves the built site)
npm run start-python

# Or use Cloudflare wrangler
npm run start
```

## Deployment

### Root Project (Hugo + PHP API)

The site is automatically deployed to Vercel on push to main/master branch via GitHub Actions.

For manual deployment:

```bash
# Build the Hugo site first
hugo --minify

# Deploy to Vercel
vercel deploy
vercel --prod
```

The `vercel.json` configuration includes:
- Automatic Hugo build command
- Hugo extended version 0.140.2
- PHP API support via vercel-php runtime

For vercel [php](https://github.com/vercel-community/php)

```bash
composer install --ignore-platform-req=ext-mbstring

php -S localhost:8000 api/index.php
```

### Subprojects (my-app and vue-client)

Each subproject has its own `vercel.json` configuration and should be deployed as separate Vercel projects:

**my-app (Next.js):**
```bash
cd my-app
vercel link  # First time only
vercel       # Preview deployment
vercel --prod  # Production deployment
```

**vue-client (Vue/Vite):**
```bash
cd vue-client
vercel link  # First time only
vercel       # Preview deployment
vercel --prod  # Production deployment
```

See individual README files in each subproject directory for more details.

For Apache

```bash
aa-teardown
sudo service apache2 restart
sudo service php8.4-fpm restart
tail -f /var/log/apache2/error.log
```

[php-framework-symfony-microservice](https://github.com/contributte/vercel-examples/tree/master/php-framework-symfony-microservice)

```bash
# Python 2
python -m SimpleHTTPServer 8001

# Python 3
python -m http.server 8001
```

### Terraform

[terraform-s3-static-website-hosting](https://www.alexhyett.com/terraform-s3-static-website-hosting)

## Testing

This project uses [Playwright](https://playwright.dev/) for end-to-end testing. The test suite includes:

- **Homepage Tests**: Validates meta tags, Open Graph tags, and page structure
- **Accessibility Tests**: Checks WCAG compliance, keyboard navigation, and theme support
- **Responsive Design Tests**: Ensures proper mobile, tablet, and desktop layouts
- **Navigation Tests**: Verifies internal/external links and navigation structure

### Running Tests Locally

```bash
# Install dependencies (includes Playwright)
npm install

# Install Playwright browsers (first time only)
npx playwright install

# Run all tests
npm test

# Run tests in headed mode (see browser)
npm run test:headed

# Run tests in interactive UI mode
npm run test:ui

# Debug tests
npm run test:debug

# View test report
npm run test:report
```

### CI/CD Integration

Tests run automatically on GitHub Actions for all push and pull request events. The workflow:

1. Sets up Node.js environment
2. Installs dependencies
3. Installs Playwright browsers
4. Runs the test suite
5. Uploads test reports and traces as artifacts

See `.github/workflows/playwright.yml` for the complete workflow configuration.

# Contributing

The [issue tracker](https://github.com/AlbanAndrieu/nabla-site-apache/issues) is the preferred channel for bug reports, features requests and submitting pull requests.

For pull requests, editor preferences are available in the [editor config](.editorconfig) for easy use in common text editors. Read more and download plugins at <http://editorconfig.org>.

## License

[Apache v2](http://www.apache.org/licenses/LICENSE-2.0.html)

---

Alban Andrieu [linkedin](https://fr.linkedin.com/in/nabla/)
