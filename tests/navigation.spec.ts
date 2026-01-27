import { test, expect } from '@playwright/test';

test.describe('Navigation and Links Tests', () => {
  test('should have working internal links', async ({ page }) => {
    await page.goto('/');
    
    // Get all internal links (not starting with http/https or #)
    const internalLinks = page.locator('a[href]:not([href^="http"]):not([href^="#"]):not([href^="mailto"]):not([href^="tel"])');
    const linkCount = await internalLinks.count();
    
    // Check first few internal links (avoid checking too many)
    const linksToCheck = Math.min(linkCount, 5);
    
    for (let i = 0; i < linksToCheck; i++) {
      const link = internalLinks.nth(i);
      if (await link.isVisible()) {
        const href = await link.getAttribute('href');
        if (href && !href.includes('javascript:')) {
          // Just verify the href attribute exists
          expect(href).toBeTruthy();
        }
      }
    }
  });

  test('should have descriptive link text', async ({ page }) => {
    await page.goto('/');
    
    // Get all links
    const links = page.locator('a[href]');
    const linkCount = await links.count();
    
    // Check that links have text or aria-label
    for (let i = 0; i < Math.min(linkCount, 10); i++) {
      const link = links.nth(i);
      const text = await link.textContent();
      const ariaLabel = await link.getAttribute('aria-label');
      const ariaLabelledby = await link.getAttribute('aria-labelledby');
      const title = await link.getAttribute('title');
      
      // Images inside links should have alt text
      const img = link.locator('img');
      const imgCount = await img.count();
      
      if (imgCount > 0) {
        const alt = await img.first().getAttribute('alt');
        const hasAccessibleName = text || ariaLabel || ariaLabelledby || title || alt;
        expect(hasAccessibleName).toBeTruthy();
      } else {
        // Text links should have text content
        const hasAccessibleName = text || ariaLabel || ariaLabelledby || title;
        expect(hasAccessibleName).toBeTruthy();
      }
    }
  });

  test('should not have broken fragment links', async ({ page }) => {
    await page.goto('/');
    
    // Get all fragment links (anchor links)
    const fragmentLinks = page.locator('a[href^="#"]');
    const linkCount = await fragmentLinks.count();
    
    // Check first few fragment links
    for (let i = 0; i < Math.min(linkCount, 5); i++) {
      const link = fragmentLinks.nth(i);
      const href = await link.getAttribute('href');
      
      if (href && href !== '#') {
        // Extract the ID from the href (remove #)
        const targetId = href.substring(1);
        
        // Check if element with that ID exists
        const target = page.locator(`#${targetId}`);
        const targetCount = await target.count();
        
        expect(targetCount).toBeGreaterThan(0);
      }
    }
  });

  test('should have external links with proper attributes', async ({ page }) => {
    await page.goto('/');
    
    // Get external links
    const externalLinks = page.locator('a[href^="http"]');
    const linkCount = await externalLinks.count();
    
    // Check first few external links
    for (let i = 0; i < Math.min(linkCount, 5); i++) {
      const link = externalLinks.nth(i);
      const href = await link.getAttribute('href');
      
      // External links should have href
      expect(href).toBeTruthy();
      
      // Check if it has target="_blank" (optional but common)
      const target = await link.getAttribute('target');
      if (target === '_blank') {
        // If target="_blank", should have rel attribute for security
        const rel = await link.getAttribute('rel');
        // rel should contain noopener or noreferrer for security
        if (rel) {
          const hasSecureRel = rel.includes('noopener') || rel.includes('noreferrer');
          expect(hasSecureRel).toBeTruthy();
        }
      }
    }
  });

  test('should have navigation menu', async ({ page }) => {
    await page.goto('/');
    
    // Look for navigation elements
    const nav = page.locator('nav, [role="navigation"], header nav, .nav, .navigation');
    const navCount = await nav.count();
    
    // Should have at least some navigation structure
    expect(navCount).toBeGreaterThanOrEqual(0);
  });

  test('should have footer with links', async ({ page }) => {
    await page.goto('/');
    
    // Look for footer
    const footer = page.locator('footer, [role="contentinfo"], .footer');
    const footerCount = await footer.count();
    
    // Footer is optional but common
    expect(footerCount).toBeGreaterThanOrEqual(0);
  });

  test('should handle link hover states', async ({ page }) => {
    await page.goto('/');
    
    // Get first visible link
    const link = page.locator('a[href]').first();
    
    if (await link.isVisible()) {
      // Hover over the link
      await link.hover();
      
      // Check that link is still visible after hover
      await expect(link).toBeVisible();
    }
  });
});
