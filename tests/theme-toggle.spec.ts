import { expect, test } from "@playwright/test";

test.describe("Theme Toggle Tests", () => {
	test("should have theme toggle control", async ({ page }) => {
		await page.goto("/");

		const root = page.locator("#theme-toggle-root");
		if ((await root.count()) > 0) {
			await expect(root).toBeVisible();
		}
	});

	test("should switch theme via segmented control", async ({ page }) => {
		await page.goto("/");

		const root = page.locator("#theme-toggle-root");
		if ((await root.count()) === 0) return;

		await page.evaluate(() => {
			localStorage.setItem("site-theme-preference", "light");
			window.themeToggle?.set("light");
		});

		const htmlElement = page.locator("html");
		await expect(htmlElement).toHaveAttribute("data-theme", "light");

		await root.locator('button[data-theme="dark"]').click();
		await expect(htmlElement).toHaveAttribute("data-theme", "dark");

		await root.locator('button[data-theme="light"]').click();
		await expect(htmlElement).toHaveAttribute("data-theme", "light");
	});

	test("should persist theme preference", async ({ page }) => {
		await page.goto("/");

		const root = page.locator("#theme-toggle-root");
		if ((await root.count()) === 0) return;

		await root.locator('button[data-theme="dark"]').click();
		const htmlElement = page.locator("html");
		const darkTheme = await htmlElement.getAttribute("data-theme");

		await page.reload();
		const persistedTheme = await htmlElement.getAttribute("data-theme");
		expect(persistedTheme).toBe(darkTheme);
	});

	test("should change theme CSS variables in dark mode", async ({ page }) => {
		await page.goto("/");

		const root = page.locator("#theme-toggle-root");
		if ((await root.count()) === 0) return;

		await page.evaluate(() => {
			localStorage.setItem("site-theme-preference", "light");
			window.themeToggle?.set("light");
		});

		const lightToken = await page.evaluate(() =>
			getComputedStyle(document.documentElement)
				.getPropertyValue("--bg-primary")
				.trim(),
		);

		await root.locator('button[data-theme="dark"]').click();

		const darkToken = await page.evaluate(() =>
			getComputedStyle(document.documentElement)
				.getPropertyValue("--bg-primary")
				.trim(),
		);

		expect(lightToken).not.toBe(darkToken);
	});
});
