import assert from "node:assert/strict";
import { access } from "node:fs/promises";
import path from "node:path";
import test from "node:test";
import { MARKETING_PAGES } from "../lib/marketingPages";

test("marketing pages reference existing html files in public/", async () => {
	for (const [slug, spec] of Object.entries(MARKETING_PAGES)) {
		assert.match(slug, /^[a-z0-9-]+$/);
		assert.match(spec.file, /^[a-z0-9-]+\.html$/);

		const fullPath = path.join(process.cwd(), "public", spec.file);
		await access(fullPath);
	}
});

test("mainOuter marketing pages keep the shared site-content-page class", () => {
	for (const [slug, spec] of Object.entries(MARKETING_PAGES)) {
		if (spec.mode !== "mainOuter") continue;
		assert.ok(spec.bodyClass, `missing bodyClass for ${slug}`);
		assert.match(
			spec.bodyClass,
			/\bsite-content-page\b/,
			`missing site-content-page class for ${slug}`,
		);
	}
});

test("high-risk marketing pages keep expected scoped body classes", () => {
	const expectedBySlug: Record<string, string> = {
		contact: "site-content-page page-contact",
		security: "site-content-page page-security page-dark",
		expertise: "site-content-page page-dark",
		ciso: "site-content-page page-ciso page-dark",
		login: "site-content-page page-login page-dark",
		ctid: "site-content-page page-ctid page-dark",
	};

	for (const [slug, expectedBodyClass] of Object.entries(expectedBySlug)) {
		assert.equal(MARKETING_PAGES[slug]?.bodyClass, expectedBodyClass);
	}
});
