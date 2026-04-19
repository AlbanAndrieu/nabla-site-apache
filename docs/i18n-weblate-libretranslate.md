# Internationalization workflow (Next.js + next-intl + Weblate + LibreTranslate)

Default locale is English (`en`) and French (`fr`) is enabled as the first translated locale.

## Runtime i18n

- `next-intl` is configured with:
  - `/...` (default locale `en`)
  - `/fr/...`
- Middleware negotiates and redirects requests to a locale route.
- UI strings are in:
  - `messages/en.json`
  - `messages/fr.json`

For static HTML pages loaded from `public/`, localized variants are resolved from:

- `public/locales/fr/<page>.html`

If a localized file does not exist, English source from `public/<page>.html` is used as fallback.

## Weblate integration

Use `messages/*.json` as source files in Weblate:

- source language: `en`
- target language: `fr`
- file mask: `messages/*.json`

Recommended component setup in Weblate:

- Add one JSON component pointing to `messages/en.json`.
- Configure French translation output as `messages/fr.json`.
- Commit synchronization back to this repository.

## LibreTranslate pre-translation

This repository includes `scripts/translate-with-libretranslate.mjs` to pre-fill French JSON from English.

Environment variables:

- `LIBRETRANSLATE_URL` (default: `https://libretranslate.com/translate`)
- `LIBRETRANSLATE_API_KEY` (optional)

Command:

- `npm run i18n:libretranslate`

Behavior:

- Reads `messages/en.json`
- Calls LibreTranslate for each leaf string
- Writes merged output to `messages/fr.json`
- Preserves existing French keys when already translated

## Add more localized HTML pages

To translate a static page, copy the English file and edit only translatable text:

1. `public/<page>.html`
2. `public/locales/fr/<page>.html`

The app automatically serves the French version on `/fr/...`.
