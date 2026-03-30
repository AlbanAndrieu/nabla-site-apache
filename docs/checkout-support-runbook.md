# Checkout and Support Flow Runbook

## Scope

Stripe **hosted Checkout**: the browser gets a Checkout Session URL from your server and redirects to Stripe; after pay or cancel, Stripe sends the customer back to your `success` or `cancel` page.

## Code paths

- `public/checkout.html` — form + `public/checkout-stripe.js` (fetch JSON `{ url }`, then `location` to Stripe)
- `public/success.html` — optional `?session_id=` from Stripe (display only unless you add verification)
- `public/cancel.html`
- `public/checkout.css`
- `server.cjs` — Express: static `public/`, `POST /create-checkout-session`
- `package.json` — `start:stripe` → `node server.cjs`

## Endpoint contract

**`POST /create-checkout-session`**

- With **`Accept: application/json`** (the default from `checkout-stripe.js`): response **`200`** and body **`{ "url": "https://checkout.stripe.com/..." }`**, or **`500`** and **`{ "error": "message" }`**.
- With a plain HTML form POST (no JS / noscript): **`303`** redirect to Stripe Checkout, or **`500`** text body on error.

## Environment

| Variable | Required | Purpose |
|----------|----------|---------|
| `STRIPE_SECRET_KEY` or `STRIPE_KEY` | Yes | Server-side Stripe API key |
| `STRIPE_PRICE_ID` or `PRICE_ID` | Yes | One-time **Price** ID (`price_...`) |
| `DOMAIN` | Optional | Site origin for success/cancel URLs (default `http://localhost:4242`), **no trailing slash** |
| `PORT` | Optional | Listen port (default `4242`) |

Example:

```bash
export STRIPE_SECRET_KEY=sk_test_...
export STRIPE_PRICE_ID=price_...
export DOMAIN=http://localhost:4242
npm run start:stripe
```

See also `.env.example` (load with your shell or a tool of your choice; this repo does not auto-load `.env` in `server.cjs`).

## Local testing

1. Start the Stripe server: `npm run start:stripe`
2. Open `http://localhost:4242/checkout.html`
3. Use [Stripe test cards](https://docs.stripe.com/testing)

`python -m http.server` **without** the Node server will not implement `/create-checkout-session`; the checkout button will error or (noscript) hit a missing endpoint.

## Success URL and `session_id`

`server.cjs` sets:

`success_url: ${DOMAIN}/success.html?session_id={CHECKOUT_SESSION_ID}`

Stripe replaces the placeholder when redirecting. The success page shows the id for support reference only. To **verify** payment server-side, add a small authenticated route that calls `stripe.checkout.sessions.retrieve(session_id)` and checks `payment_status`.

## Production / Vercel

This repository’s root `vercel.json` sends requests under `/api/` to PHP. It does **not** execute `server.cjs`. You must either:

- Run the Node server (or another implementation of the same POST contract) behind your domain and reverse-proxy `/create-checkout-session`, or  
- Add a separate serverless function (Node, etc.) that creates the session with the same JSON/redirect behaviour.

## Troubleshooting

- **500 “Missing STRIPE_PRICE_ID”** — create a Product/Price in the Dashboard and export the Price id.
- **500 “No such price”** — key mode (test vs live) must match the price mode.
- **CORS** — keep checkout page and API on the **same origin**, or add CORS + absolute `fetch` URL.
