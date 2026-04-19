import { expect, test } from "@playwright/test";

test.describe("CV privacy and stale-link regression checks", () => {
	test("homepage should not link to removed jusmundi CV pages", async ({ page }) => {
		await page.goto("/");

		await expect(page.locator('a[href*="cv/jusmundi"]')).toHaveCount(0);
		await expect(page.locator('a[href="/expertise"]')).toBeVisible();
	});

	test("cv landing should keep private summary text without legacy page link", async ({
		page,
	}) => {
		await page.goto("/cv/index.html");

		await expect(
			page.getByText("Private legal-tech engagement summary", { exact: false }),
		).toBeVisible();
		await expect(page.locator('a[href*="/cv/jusmundi/"]')).toHaveCount(0);
	});

	test("robots.txt should not include stale private review directives", async ({
		request,
	}) => {
		const response = await request.get("/robots.txt");

		expect(response.ok()).toBeTruthy();
		const robots = await response.text();

		expect(robots).not.toContain("/cv/jusmundi/4-years-review-aandrieu.html");
		expect(robots).not.toContain("/cv/jusmundi/4-years-review-jusmundi.html");
		expect(robots).not.toContain("/cv/jusmundi/yearly-review-2025-aandrieu.html");
	});
});
