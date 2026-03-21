# Checkout and Support Flow Runbook

## Scope

This runbook documents the current support and payment flow implemented in this repository, with a focus on Stripe checkout pages and developer troubleshooting.

## Codepaths Covered

- `public/checkout.html`
- `public/success.html`
- `public/cancel.html`
- `public/style.css` (checkout styles under `.checkout-page` and related selectors)
- `package.json` (`start:stripe` script)
- `server.js` (pointer comment to `server.cjs`)

## Current Architecture

The UI portion of checkout is present and static:

- `checkout.html` renders a support card and submits a `POST` form to `/create-checkout-session`.
- `success.html` and `cancel.html` provide post-checkout landing states.

The backend entry point configured by npm is:

```bash
npm run start:stripe
```

That script runs `node server.cjs`, but `server.cjs` is currently missing in this branch.

## Public Interface Contract

The frontend currently expects this server interface:

- `POST /create-checkout-session`

Because the form is plain HTML (`method="POST"` with no JavaScript interception), any backend implementation should keep this endpoint stable to avoid breaking the existing page.

## Local Setup Modes

### Static page review only

Use this when validating layout/content only:

```bash
npm run start-python
```

Then open `http://localhost:8001/checkout.html`.

Constraint: pressing "Proceed to checkout" will fail unless a compatible backend endpoint is running.

### Stripe flow (current status)

```bash
npm run start:stripe
```

Current result on this branch:

- Fails at startup with `Error: Cannot find module '/workspace/server.cjs'`.

## Common Pitfalls

- Assuming checkout is fully operational because the pages exist.
- Testing only `success.html`/`cancel.html` directly, without verifying endpoint behavior.
- Changing `public/checkout.html` form action without updating backend route compatibility.

## Recommended Next Step When Re-enabling Stripe

Add or restore `server.cjs` so that:

- It serves `public/` assets/pages for local checkout testing.
- It handles `POST /create-checkout-session`.
- It uses environment variables for Stripe credentials and base domain configuration.
