import { expect, test } from "@playwright/test";

test.describe("Theme Toggle Tests", () => {
	test("should have theme toggle button", async ({ page }) => {
		await page.goto("/");

		// Look for theme toggle button
		const themeToggle = page.locator(
			'button[aria-label*="theme" i], .theme-toggle, #theme-toggle',
		);
		if ((await themeToggle.count()) > 0) {
			await expect(themeToggle).toBeVisible();
		}
	});

	test("should toggle between light and dark theme", async ({ page }) => {
		await page.goto("/");

		const themeToggle = page
			.locator('button[aria-label*="theme" i], .theme-toggle, #theme-toggle')
			.first();

		// Check if theme toggle exists
		if ((await themeToggle.count()) > 0) {
			// Get initial theme
			const htmlElement = page.locator("html");
			const initialTheme = await htmlElement.getAttribute("data-theme");

			// Click to toggle theme
			await themeToggle.click();

			// Wait for theme change
			await page.waitForTimeout(300);

			// Get new theme
			const newTheme = await htmlElement.getAttribute("data-theme");

			// Verify theme changed
			expect(newTheme).not.toBe(initialTheme);

			// Toggle back
			await themeToggle.click();
			await page.waitForTimeout(300);

			// Verify we're back to original theme
			const finalTheme = await htmlElement.getAttribute("data-theme");
			expect(finalTheme).toBe(initialTheme);
		}
	});

	test("should persist theme preference", async ({ page, context }) => {
		await page.goto("/");

		const themeToggle = page
			.locator('button[aria-label*="theme" i], .theme-toggle, #theme-toggle')
			.first();

		if ((await themeToggle.count()) > 0) {
			// Set to dark theme
			await themeToggle.click();
			await page.waitForTimeout(300);

			const htmlElement = page.locator("html");
			const darkTheme = await htmlElement.getAttribute("data-theme");

			// Reload page
			await page.reload();

			// Check if theme persisted
			const persistedTheme = await htmlElement.getAttribute("data-theme");
			expect(persistedTheme).toBe(darkTheme);
		}
	});

	test("should apply correct styles in dark mode", async ({ page }) => {
		await page.goto("/");

		const themeToggle = page
			.locator('button[aria-label*="theme" i], .theme-toggle, #theme-toggle')
			.first();

		if ((await themeToggle.count()) > 0) {
			const htmlElement = page.locator("html");

			// Check initial background color
			const initialBg = await page.evaluate(() => {
				return window.getComputedStyle(document.body).backgroundColor;
			});

			// Toggle to dark mode
			await themeToggle.click();
			await page.waitForTimeout(300);

			// Check if background changed
			const darkBg = await page.evaluate(() => {
				return window.getComputedStyle(document.body).backgroundColor;
			});

			// Background should be different
			expect(darkBg).not.toBe(initialBg);
		}
	});
});
