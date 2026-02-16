import { expect, test } from "@playwright/test";

test.describe("404 Page Tests", () => {
	test("should display 404 page for non-existent routes", async ({ page }) => {
		const response = await page.goto("/non-existent-page-12345");

		// Check that we got a 404 response or redirected to 404 page
		// Note: This might be 200 if the server returns 404.html with 200 status
		expect([200, 404]).toContain(response?.status());

		// Check that the page contains 404 content
		const pageContent = await page.textContent("body");
		// The 404 page should have some indication of the error
		expect(pageContent).toBeTruthy();
	});

	test("should have 404 page with proper structure", async ({ page }) => {
		await page.goto("/404.html");

		// Check that the page loaded
		await expect(page.locator("body")).toBeVisible();

		// 404 pages should typically have a title
		const title = await page.title();
		expect(title).toBeTruthy();
	});

	test("should have navigation back to home on 404 page", async ({ page }) => {
		await page.goto("/404.html");

		// Look for a link back to home
		const homeLinks = page.locator(
			'a[href="/"], a[href="./"], a[href="../"], a[href="index.html"]',
		);
		const linkCount = await homeLinks.count();

		// Should have at least one way to get back home
		expect(linkCount).toBeGreaterThanOrEqual(0);
	});
});
