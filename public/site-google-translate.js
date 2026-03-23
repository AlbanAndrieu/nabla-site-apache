/**
 * Google Translate widget: ensures mount node, defines googleTranslateElementInit, loads Google script.
 *
 * Optional on <script src="/site-google-translate.js" defer>: data-no-google-translate — skip entirely.
 * If #google_translate_element is missing, a fixed-position wrapper is prepended to <body> (see theme.css).
 */
(() => {
	var root = document.currentScript;
	if (root && root.hasAttribute("data-no-google-translate")) return;
	if (window.__NABLA_GOOGLE_TRANSLATE_STARTED) return;
	window.__NABLA_GOOGLE_TRANSLATE_STARTED = true;

	var INCLUDED_LANGS = "en,fr,no,de,es,it,pt,nl,sv,da,fi,pl,cs,ru,ar,ja,zh-CN";

	function ensureMount() {
		var el = document.getElementById("google_translate_element");
		if (el) return el;
		var wrap = document.createElement("div");
		wrap.className = "google-translate-widget";
		wrap.setAttribute("aria-label", "Language translation options");
		el = document.createElement("div");
		el.id = "google_translate_element";
		wrap.appendChild(el);
		var body = document.body;
		if (body) {
			body.insertBefore(wrap, body.firstChild);
			return el;
		}
		return null;
	}

	function loadExternal() {
		if (
			document.querySelector(
				'script[src*="translate.google.com/translate_a/element.js"]',
			)
		)
			return;
		var s = document.createElement("script");
		s.src =
			"https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
		(document.head || document.documentElement).appendChild(s);
	}

	function start() {
		var mount = ensureMount();
		if (!mount) return;
		loadExternal();
	}

	window.googleTranslateElementInit = () => {
		if (window.__NABLA_GOOGLE_TRANSLATE_READY) return;
		if (
			!window.google ||
			!google.translate ||
			!google.translate.TranslateElement
		)
			return;
		var mount = document.getElementById("google_translate_element");
		if (!mount) return;
		window.__NABLA_GOOGLE_TRANSLATE_READY = true;
		new google.translate.TranslateElement(
			{
				pageLanguage: "en",
				includedLanguages: INCLUDED_LANGS,
				layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
				autoDisplay: false,
			},
			"google_translate_element",
		);
	};

	if (document.body) {
		start();
	} else {
		document.addEventListener("DOMContentLoaded", start);
	}
})();
