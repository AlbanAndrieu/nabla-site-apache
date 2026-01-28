# Layouts Directory

This directory contains Hugo template files that define how content is rendered.

## Structure

```
layouts/
├── _default/
│   ├── baseof.html    # Base template
│   ├── single.html    # Single page template
│   └── list.html      # List page template
├── partials/
│   ├── header.html
│   ├── footer.html
│   └── nav.html
├── index.html         # Homepage template
└── 404.html          # 404 error page
```

## Templates

Hugo uses Go's template syntax. Templates can access:
- `.Title` - Page title
- `.Content` - Page content
- `.Params` - Custom parameters from front matter
- `.Site.Params` - Site-wide parameters

## Current State

This directory is currently empty. During migration, HTML structure from existing pages will be converted to Hugo templates here.
