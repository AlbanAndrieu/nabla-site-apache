import { expect, test } from "@playwright/test";

test.describe("Site widgets integration", () => {
	test("injects print and back-to-top controls on homepage", async ({ page }) => {
		await page.goto("/");

		const printBtn = page.locator("#nabla-print-pdf-btn");
		const backToTop = page.locator("#nabla-back-to-top");

		await expect(printBtn).toBeVisible();
		await expect(printBtn).toHaveAttribute("aria-label", /print|pdf/i);
		await expect(backToTop).toBeVisible();
		await expect(backToTop).toHaveAttribute("href", "#top");
	});

	test("clicking print control calls window.print", async ({ page }) => {
		await page.addInitScript(() => {
			(window as unknown as { __printCalls: number }).__printCalls = 0;
			window.print = () => {
				(window as unknown as { __printCalls: number }).__printCalls += 1;
			};
		});

		await page.goto("/");

		const printBtn = page.locator("#nabla-print-pdf-btn");
		await expect(printBtn).toBeVisible();
		await printBtn.click();

		const printCalls = await page.evaluate(
			() => (window as unknown as { __printCalls: number }).__printCalls,
		);
		expect(printCalls).toBe(1);
	});

	test("minimal chrome page does not inject optional widgets", async ({ page }) => {
		await page.goto("/404.html");

		await expect(page.locator("#theme-toggle-root")).toHaveCount(0);
		await expect(page.locator("#nabla-print-pdf-btn")).toHaveCount(0);
		await expect(page.locator("#nabla-back-to-top")).toHaveCount(0);
		await expect(page.locator(".google-translate-widget")).toHaveCount(0);
	});
});
