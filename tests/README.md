# Playwright Tests

This directory contains end-to-end tests for the nabla-site-apache project using Playwright.

## Test Structure

- `homepage.spec.ts` - Tests for the homepage including meta tags and basic structure
- `accessibility.spec.ts` - Tests for accessibility features (WCAG compliance, keyboard navigation, etc.)
- `responsive.spec.ts` - Tests for responsive design across different viewports
- `navigation.spec.ts` - Tests for links, navigation, and routing

## Running Tests

```bash
# Run all tests
npm test

# Run tests in headed mode (see the browser)
npm run test:headed

# Run tests in UI mode (interactive)
npm run test:ui

# Debug tests
npm run test:debug

# View test report
npm run test:report
```

## Writing New Tests

To add new tests, create a new file with the `.spec.ts` extension in this directory:

```typescript
import { test, expect } from '@playwright/test';

test.describe('My Feature Tests', () => {
  test('should do something', async ({ page }) => {
    await page.goto('/');
    
    // Your test logic here
    await expect(page.locator('selector')).toBeVisible();
  });
});
```

## Best Practices

1. **Use descriptive test names** - Make it clear what each test is checking
2. **Keep tests independent** - Each test should be able to run on its own
3. **Use page objects for complex pages** - Extract common page interactions into reusable functions
4. **Test user workflows** - Focus on how users interact with the site
5. **Check accessibility** - Ensure all features are keyboard-accessible and screen-reader friendly
6. **Test responsive behavior** - Verify mobile, tablet, and desktop viewports

## Continuous Integration

Tests run automatically on GitHub Actions for:
- Push to main, master, or develop branches
- Pull requests to main, master, or develop branches

The workflow is defined in `.github/workflows/playwright.yml`.

## Resources

- [Playwright Documentation](https://playwright.dev/)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [Playwright API Reference](https://playwright.dev/docs/api/class-playwright)
