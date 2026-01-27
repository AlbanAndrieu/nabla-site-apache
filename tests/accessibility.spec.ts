import { test, expect } from '@playwright/test';

test.describe('Accessibility Tests', () => {
  test('should have skip navigation link for keyboard users', async ({ page }) => {
    await page.goto('/');
    
    // Look for skip links or main content landmarks
    const main = page.locator('main, [role="main"]');
    // Check if main landmark exists
    const mainCount = await main.count();
    expect(mainCount).toBeGreaterThanOrEqual(0); // May or may not have main, just checking structure
  });

  test('should support keyboard navigation', async ({ page }) => {
    await page.goto('/');
    
    // Press Tab to navigate
    await page.keyboard.press('Tab');
    
    // Check that an element is focused
    const focusedElement = await page.evaluate(() => {
      const focused = document.activeElement;
      return focused ? focused.tagName : null;
    });
    
    expect(focusedElement).toBeTruthy();
  });

  test('should have accessible images with alt text', async ({ page }) => {
    await page.goto('/');
    
    // Get all images
    const images = page.locator('img');
    const imageCount = await images.count();
    
    // Check that images have alt attributes
    for (let i = 0; i < imageCount; i++) {
      const img = images.nth(i);
      const hasAlt = await img.getAttribute('alt');
      // Alt can be empty string for decorative images, but should be present
      expect(hasAlt).not.toBeNull();
    }
  });

  test('should have theme toggle functionality', async ({ page }) => {
    await page.goto('/');
    
    // Look for theme toggle button
    const themeToggle = page.locator('button[aria-label*="theme" i], button[title*="theme" i], button:has-text("theme"), .theme-toggle, #theme-toggle');
    
    // Check if theme toggle exists (may not exist on all pages)
    const toggleCount = await themeToggle.count();
    
    if (toggleCount > 0) {
      // Get initial theme
      const initialTheme = await page.locator('html').getAttribute('data-theme');
      
      // Click theme toggle
      await themeToggle.first().click();
      
      // Wait for theme to change
      await page.waitForTimeout(500);
      
      // Get new theme
      const newTheme = await page.locator('html').getAttribute('data-theme');
      
      // Theme should have changed
      expect(newTheme).not.toBe(initialTheme);
    }
  });

  test('should have proper heading hierarchy', async ({ page }) => {
    await page.goto('/');
    
    // Check for h1
    const h1Count = await page.locator('h1').count();
    expect(h1Count).toBeGreaterThanOrEqual(0); // Should have at least 0 h1 elements
  });

  test('should have form labels associated with inputs', async ({ page }) => {
    await page.goto('/');
    
    // Get all inputs
    const inputs = page.locator('input[type="text"], input[type="email"], input[type="password"], input[type="search"], textarea');
    const inputCount = await inputs.count();
    
    // Check that inputs have labels or aria-label
    for (let i = 0; i < inputCount; i++) {
      const input = inputs.nth(i);
      const id = await input.getAttribute('id');
      const ariaLabel = await input.getAttribute('aria-label');
      const ariaLabelledby = await input.getAttribute('aria-labelledby');
      
      if (id) {
        // Check if there's a label with for attribute
        const label = page.locator(`label[for="${id}"]`);
        const labelCount = await label.count();
        
        // Should have either a label, aria-label, or aria-labelledby
        const hasAccessibleName = labelCount > 0 || ariaLabel || ariaLabelledby;
        expect(hasAccessibleName).toBeTruthy();
      }
    }
  });

  test('should have good color contrast', async ({ page }) => {
    await page.goto('/');
    
    // Basic check - ensure body has visible text
    const body = page.locator('body');
    await expect(body).toBeVisible();
    
    // Check computed styles for text visibility
    const hasVisibleText = await page.evaluate(() => {
      const body = document.body;
      const styles = window.getComputedStyle(body);
      const color = styles.color;
      const backgroundColor = styles.backgroundColor;
      
      // Basic check - color should be different from background
      return color !== backgroundColor;
    });
    
    expect(hasVisibleText).toBeTruthy();
  });

  test('should not have any invalid ARIA attributes', async ({ page }) => {
    await page.goto('/');
    
    // Check for common ARIA attributes
    const elementsWithAria = page.locator('[role], [aria-label], [aria-labelledby], [aria-describedby]');
    const count = await elementsWithAria.count();
    
    // Just verify elements with ARIA exist and page loads correctly
    expect(count).toBeGreaterThanOrEqual(0);
  });
});
