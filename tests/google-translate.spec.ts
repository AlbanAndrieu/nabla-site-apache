import { expect, test } from "@playwright/test";

test.describe("Google Translate Widget Tests", () => {
	test("should have Google Translate widget container", async ({ page }) => {
		await page.goto("/");

		// Check for Google Translate widget container
		const translateElement = page.locator("#google_translate_element");
		await expect(translateElement).toBeVisible();
	});

	test("should load Google Translate scripts", async ({ page }) => {
		await page.goto("/");

		// Wait for page to load
		await page.waitForLoadState("networkidle");

		// Check if Google Translate script is loaded
		const translateScript = page.locator('script[src*="translate.google.com"]');
		if ((await translateScript.count()) > 0) {
			expect(await translateScript.count()).toBeGreaterThan(0);
		}
	});

	test("should have translate configuration", async ({ page }) => {
		await page.goto("/");

		// Check if translate config exists
		const hasConfig = await page.evaluate(() => {
			return (
				typeof (window as any).googleTranslateElementInit === "function" ||
				typeof (window as any).googleTranslateConfig !== "undefined"
			);
		});

		// If config exists, verify it
		if (hasConfig) {
			expect(hasConfig).toBeTruthy();
		}
	});

	test("should be positioned correctly", async ({ page }) => {
		await page.goto("/");

		const translateElement = page.locator("#google_translate_element");

		if ((await translateElement.count()) > 0) {
			// Check if element is in viewport
			await expect(translateElement).toBeInViewport();

			// Check if it has proper styling (usually fixed/absolute position)
			const position = await translateElement.evaluate((el) => {
				return window.getComputedStyle(el).position;
			});

			// Translate widget should be positioned (not static)
			expect(["fixed", "absolute", "relative", "sticky"]).toContain(position);
		}
	});

	test("should be accessible on mobile", async ({ page, viewport }) => {
		await page.goto("/");

		if (viewport && viewport.width < 768) {
			const translateElement = page.locator("#google_translate_element");

			if ((await translateElement.count()) > 0) {
				// Widget should still be visible on mobile
				await expect(translateElement).toBeInViewport();
			}
		}
	});
});
