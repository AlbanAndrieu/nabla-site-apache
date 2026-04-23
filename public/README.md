# `public/` — static web root

This directory is the static asset and HTML root for the site: pages, shared CSS/JS, images, CV files, and other files served as-is (for example via `npm run start:python`, Apache, or your host’s web root mapping).

## Layout

Keep site-wide assets organized under paths such as `assets/`, `cv/`, and top-level HTML entry points (`index.html`, and so on) so deployments and Playwright tests stay predictable.
