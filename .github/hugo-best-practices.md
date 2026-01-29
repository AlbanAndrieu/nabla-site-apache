# Hugo Best Practices

This document provides comprehensive guidelines for developing, maintaining, and optimizing the Hugo static site generator setup for the Nabla site.

## Table of Contents

1. [Overview](#overview)
2. [Project Structure](#project-structure)
3. [Content Organization](#content-organization)
4. [Template Development](#template-development)
5. [Asset Management](#asset-management)
6. [Performance Optimization](#performance-optimization)
7. [SEO Best Practices](#seo-best-practices)
8. [Development Workflow](#development-workflow)
9. [Testing and Validation](#testing-and-validation)
10. [Deployment](#deployment)

## Overview

Hugo is a fast, flexible static site generator written in Go. This guide ensures our Hugo implementation is:
- **Fast**: Optimized build times and site performance
- **Maintainable**: Clear structure and conventions
- **SEO-Friendly**: Proper metadata and structure
- **Accessible**: WCAG AA compliant
- **Scalable**: Easy to add content and features

## Project Structure

### Directory Layout

```
nabla-site-apache/
├── archetypes/          # Content templates
│   └── default.md       # Default front matter template
├── content/             # Markdown content files
│   ├── _index.md        # Homepage content
│   ├── about.md         # About page
│   └── posts/           # Blog posts
│       ├── _index.md    # Posts list page
│       └── post-1.md    # Individual post
├── layouts/             # HTML templates
│   ├── _default/        # Default templates
│   │   ├── baseof.html  # Base template (all pages inherit)
│   │   ├── list.html    # List pages (sections)
│   │   └── single.html  # Single pages
│   ├── partials/        # Reusable template parts
│   │   ├── head.html    # HTML head section
│   │   ├── header.html  # Site header
│   │   └── footer.html  # Site footer
│   ├── shortcodes/      # Custom shortcodes
│   └── index.html       # Homepage template
├── static/              # Static assets (copied as-is)
│   ├── assets/          # Images, CSS, JS
│   ├── robots.txt       # Crawler instructions
│   └── favicon.ico      # Site favicon
├── themes/              # Hugo themes (if using external)
├── hugo.toml            # Hugo configuration
└── public/              # Generated site (git-ignored)
```

### Best Practices for Structure

1. **Separation of Concerns**:
   - Content (`content/`) - Markdown files
   - Templates (`layouts/`) - HTML structure
   - Assets (`static/`) - Images, CSS, JS
   - Configuration (`hugo.toml`) - Site settings

2. **Naming Conventions**:
   - Use lowercase for file/folder names
   - Use hyphens for multi-word names: `blog-post.md`, not `BlogPost.md`
   - Use `_index.md` for section index pages
   - Use descriptive names: `about.md`, not `page1.md`

3. **File Organization**:
   - Group related content in sections
   - Keep assets organized by type
   - Use subdirectories for complex sections

## Content Organization

### Front Matter

Every content file should have front matter with metadata:

```yaml
---
title: "Page Title"
date: 2024-01-29T10:00:00+01:00
draft: false
description: "Brief description for SEO and social sharing"
author: "Alban Andrieu"
tags: ["tag1", "tag2"]
categories: ["category"]
image: "/images/featured.jpg"
---

Content goes here...
```

**Required Front Matter Fields**:
- `title` - Page title (used in `<title>` and `<h1>`)
- `date` - Publication date (ISO 8601 format)

**Recommended Front Matter Fields**:
- `description` - Meta description (150-160 characters)
- `draft` - Set to `true` for unpublished content
- `author` - Content author
- `image` - Featured image for social sharing
- `tags` - For content categorization
- `slug` - Custom URL slug (optional)
- `weight` - For ordering pages in menus

### Content Structure

**Homepage** (`content/_index.md`):
```yaml
---
title: "Nabla DevSecOps"
description: "Nabla company promoting experienced DevSecOps professional"
---

Welcome to Nabla DevSecOps...
```

**Section Index** (`content/blog/_index.md`):
```yaml
---
title: "Blog"
description: "Latest DevSecOps insights and tutorials"
---

Browse our latest articles...
```

**Individual Page** (`content/blog/my-post.md`):
```yaml
---
title: "Getting Started with DevSecOps"
date: 2024-01-29T10:00:00+01:00
draft: false
description: "Learn the fundamentals of DevSecOps practices"
author: "Alban Andrieu"
tags: ["devops", "security", "tutorial"]
image: "/images/devops-cover.jpg"
---

In this post, we'll explore...
```

### Content Best Practices

1. **Use Semantic Markdown**:
   ```markdown
   # Heading 1 (only one per page, usually from front matter title)
   
   ## Heading 2 (main sections)
   
   ### Heading 3 (subsections)
   
   **Bold text** for emphasis
   
   *Italic text* for slight emphasis
   
   [Link text](https://example.com)
   
   ![Alt text](/images/image.jpg)
   
   - Unordered list
   - Item 2
   
   1. Ordered list
   2. Item 2
   
   > Blockquote for citations
   
   `Inline code`
   
   ```code
   Code block
   ```
   ```

2. **Write for Humans and SEO**:
   - Use descriptive headings (H2, H3)
   - Write clear, concise descriptions
   - Include relevant keywords naturally
   - Use alt text for all images
   - Link to related content

3. **Organize Content Logically**:
   ```
   content/
   ├── _index.md          # Homepage
   ├── about.md           # About page
   ├── services/          # Services section
   │   ├── _index.md      # Services overview
   │   ├── consulting.md  # Individual service
   │   └── training.md    # Individual service
   └── blog/              # Blog section
       ├── _index.md      # Blog index
       ├── 2024/          # Year-based organization
       │   └── post-1.md
       └── 2025/
           └── post-2.md
   ```

4. **Use Archetypes**:
   Create templates for consistent content structure.
   
   `archetypes/blog.md`:
   ```yaml
   ---
   title: "{{ replace .Name "-" " " | title }}"
   date: {{ .Date }}
   draft: true
   description: ""
   author: "Alban Andrieu"
   tags: []
   categories: []
   image: ""
   ---
   
   ## Introduction
   
   ## Main Content
   
   ## Conclusion
   ```
   
   Create new content:
   ```bash
   hugo new blog/my-new-post.md --kind blog
   ```

## Template Development

### Base Template

Create a base template that all pages inherit from:

`layouts/_default/baseof.html`:
```html
<!DOCTYPE html>
<html lang="{{ .Site.Language.Lang }}" data-theme="light">
<head>
  {{ partial "head.html" . }}
</head>
<body>
  {{ partial "header.html" . }}
  
  <main id="main-content">
    {{ block "main" . }}{{ end }}
  </main>
  
  {{ partial "footer.html" . }}
  {{ partial "scripts.html" . }}
</body>
</html>
```

### Single Page Template

`layouts/_default/single.html`:
```html
{{ define "main" }}
<article class="content-single">
  <header>
    <h1>{{ .Title }}</h1>
    {{ if .Params.author }}
      <p class="author">By {{ .Params.author }}</p>
    {{ end }}
    {{ if not .Date.IsZero }}
      <time datetime="{{ .Date.Format "2006-01-02" }}">
        {{ .Date.Format "January 2, 2006" }}
      </time>
    {{ end }}
  </header>
  
  {{ if .Params.image }}
    <img src="{{ .Params.image }}" alt="{{ .Title }}" class="featured-image">
  {{ end }}
  
  <div class="content">
    {{ .Content }}
  </div>
  
  {{ if .Params.tags }}
    <footer>
      <p>Tags:
        {{ range .Params.tags }}
          <a href="{{ "/tags/" | relLangURL }}{{ . | urlize }}">{{ . }}</a>
        {{ end }}
      </p>
    </footer>
  {{ end }}
</article>
{{ end }}
```

### List Page Template

`layouts/_default/list.html`:
```html
{{ define "main" }}
<div class="content-list">
  <h1>{{ .Title }}</h1>
  {{ .Content }}
  
  {{ range .Pages }}
    <article class="summary">
      <h2><a href="{{ .RelPermalink }}">{{ .Title }}</a></h2>
      {{ if .Params.description }}
        <p>{{ .Params.description }}</p>
      {{ else }}
        <p>{{ .Summary }}</p>
      {{ end }}
      <a href="{{ .RelPermalink }}" class="read-more">Read more →</a>
    </article>
  {{ end }}
</div>
{{ end }}
```

### Partials

**Head Partial** (`layouts/partials/head.html`):
```html
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ if .IsHome }}{{ .Site.Title }}{{ else }}{{ .Title }} | {{ .Site.Title }}{{ end }}</title>

<!-- SEO Meta Tags -->
<meta name="description" content="{{ if .Params.description }}{{ .Params.description }}{{ else }}{{ .Site.Params.description }}{{ end }}">
<meta name="author" content="{{ if .Params.author }}{{ .Params.author }}{{ else }}{{ .Site.Params.author }}{{ end }}">

<!-- Open Graph -->
<meta property="og:title" content="{{ .Title }}">
<meta property="og:description" content="{{ if .Params.description }}{{ .Params.description }}{{ else }}{{ .Site.Params.description }}{{ end }}">
<meta property="og:type" content="{{ if .IsPage }}article{{ else }}website{{ end }}">
<meta property="og:url" content="{{ .Permalink }}">
{{ if .Params.image }}
<meta property="og:image" content="{{ .Params.image | absURL }}">
{{ end }}

<!-- Stylesheets -->
<link rel="stylesheet" href="{{ "assets/css/main.css" | relURL }}">
{{ if .Site.Params.theme }}
<link rel="stylesheet" href="{{ "assets/css/theme.css" | relURL }}">
{{ end }}

<!-- Favicon -->
<link rel="icon" href="{{ "favicon.ico" | relURL }}" type="image/x-icon">
```

### Template Best Practices

1. **Use Hugo Functions**:
   - `.RelPermalink` for internal links
   - `.RelURL` for static assets
   - `.absURL` for absolute URLs (feeds, social sharing)
   - `partial` for reusable components
   - `block` for template inheritance

2. **Handle Missing Data**:
   ```html
   {{ if .Params.author }}
     <p>By {{ .Params.author }}</p>
   {{ end }}
   
   {{ with .Params.image }}
     <img src="{{ . }}" alt="{{ $.Title }}">
   {{ end }}
   ```

3. **Use Range for Lists**:
   ```html
   {{ range .Pages }}
     <article>
       <h2>{{ .Title }}</h2>
       <a href="{{ .RelPermalink }}">Read more</a>
     </article>
   {{ end }}
   ```

4. **Conditional Rendering**:
   ```html
   {{ if eq .Type "blog" }}
     <!-- Blog-specific layout -->
   {{ else if eq .Type "services" }}
     <!-- Services-specific layout -->
   {{ else }}
     <!-- Default layout -->
   {{ end }}
   ```

5. **Accessibility in Templates**:
   ```html
   <!-- Semantic HTML -->
   <header role="banner">
   <nav role="navigation" aria-label="Main navigation">
   <main role="main" id="main-content">
   <footer role="contentinfo">
   
   <!-- Skip link -->
   <a href="#main-content" class="skip-link">Skip to main content</a>
   
   <!-- Alt text for images -->
   <img src="{{ .Params.image }}" alt="{{ .Params.imageAlt | default .Title }}">
   
   <!-- ARIA labels -->
   <button aria-label="Toggle theme">🌓</button>
   ```

### Shortcodes

Create custom shortcodes for reusable content blocks:

`layouts/shortcodes/notice.html`:
```html
<div class="notice notice-{{ .Get 0 }}">
  {{ .Inner | markdownify }}
</div>
```

Usage in content:
```markdown
{{< notice info >}}
This is an informational notice.
{{< /notice >}}
```

## Asset Management

### CSS Organization

**Option 1: Traditional CSS**
```
static/assets/css/
├── main.css         # Main styles
├── theme.css        # Theme/dark mode
├── print.css        # Print styles
└── vendor/          # Third-party CSS
```

**Option 2: Hugo Pipes (Recommended)**
```
assets/css/
├── main.scss        # Main Sass file
├── _variables.scss  # Variables
├── _mixins.scss     # Mixins
├── _base.scss       # Base styles
└── _components.scss # Components
```

Process with Hugo Pipes:
```html
{{ $style := resources.Get "css/main.scss" | toCSS | minify | fingerprint }}
<link rel="stylesheet" href="{{ $style.RelPermalink }}" integrity="{{ $style.Data.Integrity }}">
```

### JavaScript

**Organize scripts**:
```
static/assets/js/
├── main.js          # Main application
├── theme.js         # Theme switcher
└── analytics.js     # Analytics
```

**Or use Hugo Pipes**:
```html
{{ $js := resources.Get "js/main.js" | js.Build | minify | fingerprint }}
<script src="{{ $js.RelPermalink }}" integrity="{{ $js.Data.Integrity }}"></script>
```

### Image Optimization

**Use Hugo's Image Processing**:
```html
{{ with .Resources.GetMatch "featured.jpg" }}
  {{ $image := .Resize "800x" }}
  <img src="{{ $image.RelPermalink }}" alt="{{ $.Title }}" width="{{ $image.Width }}" height="{{ $image.Height }}">
{{ end }}
```

**Responsive Images**:
```html
{{ with .Resources.GetMatch "hero.jpg" }}
  {{ $small := .Resize "400x" }}
  {{ $medium := .Resize "800x" }}
  {{ $large := .Resize "1200x" }}
  <img
    src="{{ $medium.RelPermalink }}"
    srcset="{{ $small.RelPermalink }} 400w,
            {{ $medium.RelPermalink }} 800w,
            {{ $large.RelPermalink }} 1200w"
    sizes="(max-width: 400px) 400px,
           (max-width: 800px) 800px,
           1200px"
    alt="{{ $.Title }}">
{{ end }}
```

**Best Practices**:
1. Optimize images before adding to repository
2. Use appropriate formats (WebP for modern browsers)
3. Provide alt text for all images
4. Use lazy loading for below-fold images
5. Generate multiple sizes for responsive design

## Performance Optimization

### Build Configuration

`hugo.toml`:
```toml
baseURL = 'https://nabla.albandrieu.com/'
languageCode = 'en-us'
title = 'Nabla DevSecOps'

# Performance optimizations
[build]
  writeStats = true
  useResourceCacheWhen = 'always'

[caches]
  [caches.getjson]
    dir = ':cacheDir/:project'
    maxAge = '1h'
  [caches.getcsv]
    dir = ':cacheDir/:project'
    maxAge = '1h'
  [caches.images]
    dir = ':resourceDir/_gen'
    maxAge = -1

# Minification
[minify]
  disableCSS = false
  disableHTML = false
  disableJS = false
  disableJSON = false
  disableSVG = false
  disableXML = false
  minifyOutput = true
  [minify.tdewolff.html]
    keepWhitespace = false
```

### Build Command

```bash
# Development build (fast)
hugo server -D

# Production build (optimized)
hugo --minify --gc

# Production with verbose output
hugo --minify --gc --verbose
```

### Performance Best Practices

1. **Minimize Build Time**:
   - Use `.BuildDate` sparingly (triggers rebuilds)
   - Cache external data (GetJSON, getCSV)
   - Use `--gc` flag to clean up unused files
   - Limit use of `.Site.RegularPages` in templates

2. **Minimize Page Weight**:
   - Minify CSS, JS, HTML
   - Compress images
   - Use Hugo's image processing
   - Implement lazy loading
   - Defer non-critical JavaScript

3. **Implement Caching**:
   - Set appropriate cache headers in `vercel.json`
   - Use content-based hashing (fingerprinting)
   - Cache static assets aggressively

4. **Optimize Templates**:
   ```html
   <!-- Bad: Loops through all pages multiple times -->
   {{ range .Site.RegularPages }}
     {{ range .Site.RegularPages }}
       <!-- Expensive operation -->
     {{ end }}
   {{ end }}
   
   <!-- Good: Assign to variable, use once -->
   {{ $pages := .Site.RegularPages }}
   {{ range $pages }}
     <!-- Operation -->
   {{ end }}
   ```

## SEO Best Practices

### Essential SEO Elements

1. **Page Titles**:
   ```html
   <title>{{ if .IsHome }}{{ .Site.Title }}{{ else }}{{ .Title }} | {{ .Site.Title }}{{ end }}</title>
   ```

2. **Meta Descriptions**:
   ```html
   <meta name="description" content="{{ if .Params.description }}{{ .Params.description }}{{ else }}{{ .Site.Params.description }}{{ end }}">
   ```

3. **Canonical URLs**:
   ```html
   <link rel="canonical" href="{{ .Permalink }}">
   ```

4. **Structured Data**:
   `layouts/partials/schema.html`:
   ```html
   <script type="application/ld+json">
   {
     "@context": "https://schema.org",
     "@type": "WebSite",
     "name": "{{ .Site.Title }}",
     "url": "{{ .Site.BaseURL }}",
     "description": "{{ .Site.Params.description }}"
   }
   </script>
   ```

5. **RSS Feed**:
   Hugo generates RSS automatically. Customize in `layouts/_default/rss.xml`.

### Sitemap Configuration

Hugo generates `sitemap.xml` automatically. Customize:

`hugo.toml`:
```toml
[sitemap]
  changefreq = 'monthly'
  filename = 'sitemap.xml'
  priority = 0.5
```

Override per page in front matter:
```yaml
sitemap:
  changefreq: weekly
  priority: 0.8
```

### robots.txt

Hugo can generate `robots.txt`:

`hugo.toml`:
```toml
enableRobotsTXT = true
```

Or use static file: `static/robots.txt`

## Development Workflow

### Local Development

```bash
# Start development server
hugo server -D

# Start with drafts and future posts
hugo server -D -F

# Start on different port
hugo server -D --port 1314

# Start with verbose output
hugo server -D --verbose

# Start with specific config
hugo server -D --config hugo.dev.toml
```

**Development server features**:
- Live reload on file changes
- Shows draft content with `-D`
- Fast rebuilds (only changed files)
- Accessible at http://localhost:1313

### Content Creation Workflow

```bash
# Create new post
hugo new blog/my-post.md

# Create with specific archetype
hugo new --kind post blog/my-post.md

# Edit content (use your preferred editor)
code content/blog/my-post.md

# Preview with live server
hugo server -D

# When ready, set draft: false in front matter
# Or remove draft field entirely
```

### Testing Before Deploy

```bash
# Build production version locally
hugo --minify

# Check generated files
ls -la public/

# Test with local server
cd public && python3 -m http.server 8000

# Check for broken links (install htmltest)
htmltest public/
```

### Version Control

**What to commit**:
- ✅ `content/`
- ✅ `layouts/`
- ✅ `static/`
- ✅ `hugo.toml`
- ✅ `archetypes/`
- ✅ `.github/workflows/`

**What to ignore** (`.gitignore`):
- ❌ `public/` (generated files)
- ❌ `.hugo_build.lock` (build artifact)
- ❌ `resources/` (generated resources)
- ❌ `.vercel/` (deployment config)

## Testing and Validation

### Manual Testing

1. **Build test**:
   ```bash
   hugo --minify
   ```

2. **Content verification**:
   - Check all pages render correctly
   - Verify images load
   - Test internal links
   - Check responsive design

3. **Accessibility testing**:
   - Test keyboard navigation
   - Verify alt text on images
   - Check color contrast
   - Test with screen reader

### Automated Testing

**HTML Validation**:
```bash
# Install validator
npm install -g html-validator-cli

# Validate generated HTML
html-validator --file=public/index.html
```

**Link Checking**:
```bash
# Install htmltest
brew install htmltest  # macOS

# Check for broken links
htmltest public/
```

**Lighthouse Testing**:
```bash
# Install Lighthouse CI
npm install -g @lhci/cli

# Run Lighthouse audit
lhci autorun --collect.url=http://localhost:1313
```

## Deployment

### Build for Production

```bash
# Full production build
hugo --minify --gc --verbose

# Verify output
ls -la public/
find public/ -type f | wc -l
```

### CI/CD Pipeline

See `.github/workflows/hugo-deploy.yml` for automated deployment:

1. **Build**: Hugo builds site in GitHub Actions
2. **Test**: Run validation checks
3. **Deploy**: Deploy to Vercel (main/master only)

### Manual Deployment

```bash
# Build site
hugo --minify

# Deploy to Vercel
vercel --prod --prebuilt

# Or deploy to other platforms
# netlify deploy --prod --dir=public
# aws s3 sync public/ s3://your-bucket/
```

### Post-Deployment Checks

1. Verify site loads correctly
2. Test navigation and links
3. Check mobile responsiveness
4. Verify SSL certificate
5. Test forms and interactive features
6. Check analytics tracking

## Quick Reference

### Common Hugo Commands

```bash
hugo new site mysite              # Create new Hugo site
hugo new content/page.md          # Create new content
hugo server -D                    # Start dev server
hugo --minify                     # Build for production
hugo --help                       # Show help
hugo version                      # Show Hugo version
```

### Useful Hugo Variables

- `.Title` - Page title
- `.Content` - Page content (rendered)
- `.Summary` - Auto-generated summary
- `.Params` - Front matter parameters
- `.Site` - Site configuration
- `.RelPermalink` - Relative permalink
- `.Date` - Publication date
- `.IsHome` - True if homepage
- `.IsPage` - True if regular page

### Front Matter Quick Reference

```yaml
title: "Page Title"               # Required
date: 2024-01-29T10:00:00+01:00  # Required
draft: false                      # Publish status
description: "SEO description"    # Meta description
author: "Author Name"             # Content author
tags: ["tag1", "tag2"]           # Tags
image: "/images/cover.jpg"        # Featured image
weight: 10                        # Ordering
slug: "custom-url"               # Custom URL
type: "post"                     # Content type
```

## Additional Resources

- **Hugo Documentation**: https://gohugo.io/documentation/
- **Hugo Themes**: https://themes.gohugo.io/
- **Hugo Discourse**: https://discourse.gohugo.io/
- **Hugo GitHub**: https://github.com/gohugoio/hugo
- **Project Documentation**:
  - [HUGO_MIGRATION.md](../HUGO_MIGRATION.md)
  - [HUGO_QUICKSTART.md](../HUGO_QUICKSTART.md)
  - [README.md](../README.md)

## Support

For issues specific to this project:
- **GitHub Issues**: Open an issue in this repository
- **Email**: alban.andrieu@free.fr
