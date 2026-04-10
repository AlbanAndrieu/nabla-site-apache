import { readFile } from "node:fs/promises";
import path from "node:path";
import type { Metadata } from "next";

export type HtmlExtractMode = "main" | "headerMain" | "mainOuter";

function decodeBasicEntities(text: string): string {
	return text
		.replace(/&amp;/g, "&")
		.replace(/&quot;/g, '"')
		.replace(/&#39;/g, "'")
		.replace(/&lt;/g, "<")
		.replace(/&gt;/g, ">");
}

/** Rewrite internal *.html links to clean paths for the Next.js app. */
export function rewriteLegacyHtmlHrefs(fragment: string): string {
	return fragment.replace(/\bhref="([^"]*)"/gi, (full, raw: string) => {
		const href = raw.trim();
		if (
			/^(https?:|mailto:|tel:|#|javascript:|data:)/i.test(href) ||
			href.length === 0
		) {
			return full;
		}

		const ref = href.match(/^([^?#]*)(\?[^#]*)?(#.*)?$/);
		let pathPart = ref?.[1] ?? href;
		const query = ref?.[2] ?? "";
		const hash = ref?.[3] ?? "";

		while (pathPart.startsWith("../")) pathPart = pathPart.slice(3);
		while (pathPart.startsWith("./")) pathPart = pathPart.slice(2);

		if (!pathPart.endsWith(".html")) {
			return full;
		}

		const noExt = pathPart.slice(0, -5);
		const segments = noExt.split("/").filter(Boolean);
		const last = segments[segments.length - 1];
		let out: string;
		if (last === "index") {
			segments.pop();
			out = segments.length ? `/${segments.join("/")}` : "/";
		} else {
			out = `/${noExt.replace(/^\/+/, "")}`;
		}

		return `href="${out}${query}${hash}"`;
	});
}

export async function loadPublicHtmlFragment(
	file: string,
	mode: HtmlExtractMode,
): Promise<string> {
	const full = path.join(process.cwd(), "public", file);
	const html = await readFile(full, "utf8");
	let fragment = "";

	if (mode === "main") {
		const m = html.match(/<main[^>]*>([\s\S]*?)<\/main>/i);
		fragment = m?.[1] ?? "";
	} else if (mode === "headerMain") {
		const m = html.match(
			/<header[^>]*>[\s\S]*?<\/header>\s*<main[^>]*>[\s\S]*?<\/main>/i,
		);
		fragment = m?.[0] ? `<div class="site-content-page">${m[0]}</div>` : "";
	} else {
		const m = html.match(/<main[^>]*>[\s\S]*?<\/main>/i);
		fragment = m?.[0] ?? "";
	}

	return rewriteLegacyHtmlHrefs(fragment);
}

export async function metadataFromPublicHtml(
	file: string,
	canonicalPath: string,
): Promise<Metadata> {
	const full = path.join(process.cwd(), "public", file);
	const html = await readFile(full, "utf8");
	const titleMatch = html.match(/<title>([^<]*)<\/title>/i);
	const descMatch = html.match(
		/<meta\s+name="description"\s+content="([^"]*)"/i,
	);
	const title = titleMatch?.[1]
		? decodeBasicEntities(titleMatch[1].trim())
		: undefined;
	const description = descMatch?.[1]
		? decodeBasicEntities(descMatch[1].trim())
		: undefined;

	const canonical = `https://dr-alban.com${canonicalPath.startsWith("/") ? canonicalPath : `/${canonicalPath}`}`;

	return {
		title,
		description,
		alternates: { canonical },
		openGraph: title
			? { title, description, url: canonical }
			: { url: canonical },
	};
}
