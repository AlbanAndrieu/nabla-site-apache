# Frontend Runtime Scripts Runbook

## Scope

This runbook documents the shared frontend runtime scripts that were consolidated for this repository. It focuses on how pages should wire analytics, UI widgets, and translation without reintroducing duplicated inline snippets.

## Codepaths Covered

- `public/site-analytics.js`
- `public/site-widgets.js`
- `public/site-google-translate.js`
- `public/analytics-stubs.js` (legacy compatibility shim)
- `public/theme-toggle.js`
- `public/*.html` pages that load these scripts (for integration examples)

## Runtime Architecture

The current frontend runtime is split into focused scripts:

- `site-analytics.js`: analytics and telemetry loaders by mode.
- `site-widgets.js`: shared UI behavior (theme, translate bootstrap, scroll helpers, print button, back-to-top, coffee FAB, consent, optional Intercom).
- `theme-toggle.js`: early theme preference application and segmented theme control injection.
- `site-google-translate.js`: standalone translate bootstrap kept for compatibility with older pages.

`site-widgets.js` and `site-google-translate.js` both protect against double initialization through `window.__NABLA_GOOGLE_TRANSLATE_STARTED`.

## Analytics Modes (`site-analytics.js`)

Use `data-analytics-mode` on the script tag:

- `vercel` (default): Vercel analytics + Vercel speed insights.
- `full`: GTM + GA4 + VWO + PostHog + Heap + Datadog RUM + Vercel.
- `marketing`: same as `full` without Datadog RUM.
- `home`: Mixpanel + everything in `full`.

Optional analytics attributes:

- `data-ahrefs-key`: adds Ahrefs Web Analytics.
- `data-gtm-id`: override GTM container.
- `data-ga-measurement-id`: override GA4 measurement id.

Programmatic fallback is also supported through `window.NABLA_ANALYTICS_PRESET`.

### Verified page examples

- `home`: `public/index.html`
- `full`: `public/link.html`, `public/nabla.html`, `public/cv/index.html`
- `marketing`: `public/truenas.html`, `public/freenas.html`
- `vercel`: `public/ai.html`, `public/contact.html`, `public/security.html`, `public/pricing.html`

## Shared UI Attributes (`site-widgets.js`)

Frequently used attributes:

- `data-print-pdf`: inject print button.
- `data-no-print-pdf`: disable print button.
- `data-coffee-fab`: inject support floating action button.
- `data-no-coffee-fab`: disable support button.
- `data-axeptio`: load Axeptio consent SDK.
- `data-no-google-translate`: skip translate bootstrap.
- `data-no-scroll-reveal`: disable reveal animations.
- `data-minimal-chrome`: minimal UI mode (no theme toggle button, no translate, no print, no back-to-top, no Axeptio).

Default behavior to remember:

- Back-to-top is injected unless disabled (`data-no-back-to-top`).
- Intercom is only loaded when `data-intercom-app-id` is set.
- Theme preference is stored under `site-theme-preference` and reflected via `data-theme` on `<html>`.

### Verified page examples

- Standard page with print/coffee/consent: `public/index.html`, `public/contact.html`
- Checkout pages suppressing print/coffee: `public/checkout.html`, `public/cancel.html`
- Minimal chrome mode: `public/404.html`

## Recommended Integration Pattern

Paths below are for `public/*.html` at **site root**. For `public/cv/index.html` use `../theme-toggle.js`, `../site-widgets.js`, etc.; for `public/cv/jusmundi/*.html` use `../../…`. Same rule as shared CSS. Never use `/site-widgets.js`-style root paths for these files.

For content pages in `public/` (root-level HTML):

```html
<script src="theme-toggle.js"></script>
<script src="site-google-translate.js" defer></script>
<script src="site-analytics.js" data-analytics-mode="vercel"></script>
```

End of `<body>` (before `</body>`):

```html
<script
  src="site-widgets.js"
  defer
  data-print-pdf
  data-coffee-fab
  data-axeptio
></script>
```

For minimal utility pages:

```html
<script src="theme-toggle.js"></script>
<script src="site-google-translate.js" defer></script>
<script src="site-analytics.js" data-analytics-mode="vercel"></script>
<!-- … -->
<script src="site-widgets.js" defer data-no-print-pdf data-no-coffee-fab></script>
```

For root `public/404.html` (minimal chrome):

```html
<script src="site-widgets.js" defer data-minimal-chrome></script>
```

## Troubleshooting

Theme toggle not visible:

- Confirm `theme-toggle.js` is present.
- Confirm page is not using `data-minimal-chrome`.

Translate widget missing:

- Check for `data-no-google-translate` or `data-minimal-chrome`.
- If both `site-widgets.js` and `site-google-translate.js` are loaded, only one init runs by design.

Print button missing:

- Confirm `data-print-pdf` is present and `data-no-print-pdf` is not set.

Back-to-top missing:

- Confirm `data-no-back-to-top` is not set.
- Confirm the page does not use `data-minimal-chrome`.

Analytics mode mismatch:

- Verify the exact `data-analytics-mode` value on the page script tag.
- Use page examples above to keep mode selection consistent.

## Migration and Compatibility Notes

- `public/analytics-stubs.js` is deprecated and only exists so legacy pages keep loading `site-analytics.js` in `vercel` mode.
- If you touch an old page, replace `analytics-stubs.js` with direct `site-analytics.js` usage.
- Prefer script attributes over inline third-party snippets to keep runtime behavior centralized.
