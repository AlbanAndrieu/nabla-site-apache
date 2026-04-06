## <!-- markdown-link-check-disable-next-line -->

## layout: default

Text can be **bold**, _italic_, or ~~strikethrough~~.

# [![Nabla](https://dr-alban.com/assets/nabla/nabla-4.png)](https://github.com/AlbanAndrieu/nabla-site-apache) nabla-site-apache

[![License: APACHE](http://img.shields.io/:license-apache-blue.svg?style=flat-square)](http://www.apache.org/licenses/LICENSE-2.0.html)

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/S6S61UUL97)

[CHANGELOG](./CHANGELOG.html).

## Project Goal

This is a simple HTML project for Nabla company that promotes Alban Andrieu as an experienced DevSecOps professional.

Default nabla files for apache

### GitHub Actions
The repository now includes a Hugo build and deployment workflow (`.github/workflows/hugo-deploy.yml`) that:
1. Builds the Hugo site
2. Uploads build artifacts
3. Deploys to Vercel (on push to main/master branch)

Required secrets for GitHub Actions:
- `VERCEL_TOKEN`: Vercel authentication token
- `VERCEL_ORG_ID`: Your Vercel organization ID
- `VERCEL_PROJECT_ID`: Your Vercel project ID

## Monorepo Structure

This repository contains multiple deployable projects:

1. **Root Project** - PHP API + Static HTML site
2. **my-app/** - Next.js application (separate Vercel deployment)
3. **vue-client/** - Vue/Vite application (separate Vercel deployment)

### CV Documentation

For CV architecture, build workflow, and troubleshooting, see
[`public/cv/README.md`](public/cv/README.md).

### Frontend Runtime Documentation

For analytics mode selection, shared widget attributes, script integration examples, and troubleshooting, see
[`docs/frontend-runtime-scripts-runbook.md`](docs/frontend-runtime-scripts-runbook.md).

## Deployment

### Root Project (PHP + Static Site)

```bash
npm run start-python
# Cloudflare wrangler
npm run start
```

For vercel

```
vercel deploy
vercel dev
# php -S localhost:8000 api/index.php
vercel --prod
```

For vercel [php](https://github.com/vercel-community/php)

```bash
composer install --ignore-platform-req=ext-mbstring

php -S localhost:8000 api/index.php
```

### Stripe Checkout

Pages:

- `public/checkout.html` — starts hosted Checkout (`public/checkout-stripe.js` + `POST /create-checkout-session`)
- `public/success.html` — return URL after payment (`session_id` query param when configured on the server)
- `public/cancel.html` — return URL if the customer abandons Checkout

Backend (local / your own Node host):

```bash
export STRIPE_SECRET_KEY=sk_test_...
export STRIPE_PRICE_ID=price_...
export DOMAIN=http://localhost:4242
npm run start:stripe
```

Then open `http://localhost:4242/checkout.html`. The secret key and Price ID come from the [Stripe Dashboard](https://dashboard.stripe.com/); `DOMAIN` must match the origin users use (no trailing slash).

Deployment note: Vercel serves `POST /create-checkout-session` via `api/create-checkout-session.js` (rewritten from the site root per `vercel.json`). Set `STRIPE_SECRET_KEY` and `STRIPE_PRICE_ID` in the Vercel project. Local dev still uses `npm run start:stripe` (`server.cjs`).

Details: `docs/checkout-support-runbook.md`.

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

### SDLC Integration

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
