# Content Directory

This directory will contain all Markdown content files for the Hugo site.

## Structure

Content organization follows Hugo's conventions:

```
content/
├── _index.md          # Homepage content
├── about.md           # About page
├── contact.md         # Contact page
└── posts/             # Blog posts (if needed)
    └── first-post.md
```

## Front Matter

Each content file should have front matter at the top:

```markdown
+++
title = "Page Title"
date = 2026-01-28
draft = false
description = "Page description for SEO"
+++

# Page content here in Markdown
```

## Current State

This directory is currently empty. During migration, HTML content from `public/` will be converted to Markdown files here.
