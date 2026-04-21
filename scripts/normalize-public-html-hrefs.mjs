/**
 * Normalize internal hrefs under public/ (.html files) to extensionless paths (Next-friendly).
 * Keep FILE_TO_PATH in sync with lib/marketingPages.ts (+ checkout, contact).
 * Run: node scripts/normalize-public-html-hrefs.mjs
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, "..");
const publicDir = path.join(root, "public");

/** Marketing HTML basename → URL segment (same keys as MARKETING_PAGES + checkout + contact) */
const FILE_TO_PATH = {
	"ai.html": "ai",
	"security.html": "security",
	"expertise.html": "expertise",
	"workstation.html": "workstation",
	"startup.html": "startup",
	"startup-thanks.html": "startup-thanks",
	"pricing.html": "pricing",
	"success.html": "success",
	"cancel.html": "cancel",
	"payment.html": "payment",
	"ciso.html": "ciso",
	"nabla.html": "nabla",
	"login.html": "login",
	"link.html": "link",
	"ctid.html": "ctid",
	"freenas.html": "freenas",
	"truenas.html": "truenas",
	"test.html": "test",
	"example-js.html": "example-js",
	"email-contact-addresses.html": "email-contact-addresses",
	"checkout.html": "checkout",
	"contact.html": "contact",
};

const POLICY_FILES = [
	"legal",
	"impressum",
	"privacy_policy",
	"service_terms",
	"cookie_policy",
	"accessibility_statement",
];

const SKIP_DIR = new Set(["node_modules"]);

function walk(dir, relBase, out) {
	for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
		if (SKIP_DIR.has(ent.name)) continue;
		const abs = path.join(dir, ent.name);
		const rel = relBase ? `${relBase}/${ent.name}` : ent.name;
		if (ent.isDirectory()) {
			if (rel.startsWith("assets/nabla/signature")) continue;
			walk(abs, rel, out);
		} else if (ent.name.endsWith(".html")) {
			out.push(rel.replace(/\\/g, "/"));
		}
	}
}

function rewriteIndexByLocation(content, fileRel) {
	const isRoot = /^[^/]+\.html$/.test(fileRel);
	const inCv = fileRel.startsWith("cv/");
	const inJusmundi = fileRel.startsWith("cv/jusmundi/");
	const inPolicy = fileRel.startsWith("policy/");

	let c = content;
	if (isRoot) {
		c = c.replace(/\bhref="index\.html"/gi, 'href="/"');
		c = c.replace(/\bhref='index\.html'/gi, "href='/'");
	} else if (inJusmundi) {
		c = c.replace(/\bhref="index\.html"/gi, 'href="/cv/jusmundi"');
		c = c.replace(/\bhref='index\.html'/gi, "href='/cv/jusmundi'");
	} else if (inCv) {
		c = c.replace(/\bhref="index\.html"/gi, 'href="/cv"');
		c = c.replace(/\bhref='index\.html'/gi, "href='/cv'");
	}

	if (inPolicy) {
		c = c.replace(/\bhref="\.\.\/index\.html"/gi, 'href="/"');
		c = c.replace(/\bhref='\.\.\/index\.html'/gi, "href='/'");
	}

	if (fileRel === "cv/index.html") {
		c = c.replace(/\bhref="\.\.\/index\.html"/gi, 'href="/"');
		c = c.replace(/\bhref='\.\.\/index\.html'/gi, "href='/'");
	}

	if (inJusmundi) {
		c = c.replace(/\bhref="\.\.\/index\.html"/gi, 'href="/cv"');
		c = c.replace(/\bhref='\.\.\/index\.html'/gi, "href='/cv'");
	}

	return c;
}

function rewriteRootSiblingPages(content, fileRel) {
	if (!/^[^/]+\.html$/.test(fileRel)) return content;
	let c = content;
	const entries = Object.entries(FILE_TO_PATH).sort(
		(a, b) => b[0].length - a[0].length,
	);
	for (const [file, slug] of entries) {
		const esc = file.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
		const reD = new RegExp(`\\bhref="${esc}([#?][^"]*)?"`, "gi");
		c = c.replace(reD, (_, suf = "") => `href="/${slug}${suf}"`);
		const reS = new RegExp(`\\bhref='${esc}([#?][^']*)?'`, "gi");
		c = c.replace(reS, (_, suf = "") => `href='/${slug}${suf}'`);
	}
	return c;
}

function rewriteAbsoluteSiteHtml(content) {
	let c = content;
	c = c.replace(/\bhref="\/index\.html"/gi, 'href="/"');
	c = c.replace(/\bhref='\/index\.html'/gi, "href='/'");
	c = c.replace(/\bhref="\/cv\/index\.html"/gi, 'href="/cv"');
	c = c.replace(/\bhref='\/cv\/index\.html'/gi, "href='/cv'");

	for (const name of POLICY_FILES) {
		const reD = new RegExp(
			`\\bhref="\\/policy\\/${name}\\.html([#?][^"]*)?"`,
			"gi",
		);
		c = c.replace(reD, (_, suf = "") => `href="/policy/${name}${suf}"`);
		const reS = new RegExp(
			`\\bhref='\\/policy\\/${name}\\.html([#?][^']*)?'`,
			"gi",
		);
		c = c.replace(reS, (_, suf = "") => `href='/policy/${name}${suf}'`);
		c = c.replaceAll(`href="policy/${name}.html"`, `href="/policy/${name}"`);
		c = c.replaceAll(`href='policy/${name}.html'`, `href='/policy/${name}'`);
	}

	const entries = Object.entries(FILE_TO_PATH).sort(
		(a, b) => b[0].length - a[0].length,
	);
	for (const [file, slug] of entries) {
		const base = file.replace(/\.html$/i, "");
		const reD = new RegExp(`\\bhref="\\/${base}\\.html([#?][^"]*)?"`, "gi");
		c = c.replace(reD, (_, suf = "") => `href="/${slug}${suf}"`);
		const reS = new RegExp(`\\bhref='\\/${base}\\.html([#?][^']*)?'`, "gi");
		c = c.replace(reS, (_, suf = "") => `href='/${slug}${suf}'`);
	}

	return c;
}

function rewriteDrAlbanCanonicalUrls(content) {
	return content
		.replaceAll("https://dr-alban.com/index.html", "https://dr-alban.com/")
		.replaceAll(
			"https://dr-alban.com/cv/index.html",
			"https://dr-alban.com/cv",
		);
}

function transform(content, fileRel) {
	let c = content;
	c = rewriteIndexByLocation(c, fileRel);
	c = rewriteRootSiblingPages(c, fileRel);
	c = rewriteAbsoluteSiteHtml(c);
	c = rewriteDrAlbanCanonicalUrls(c);
	return c;
}

const files = [];
walk(publicDir, "", files);

let changed = 0;
for (const rel of files) {
	const abs = path.join(publicDir, rel);
	const raw = fs.readFileSync(abs, "utf8");
	const next = transform(raw, rel);
	if (next !== raw) {
		fs.writeFileSync(abs, next, "utf8");
		changed++;
		console.log(rel);
	}
}

console.log(`Updated ${changed} file(s).`);
