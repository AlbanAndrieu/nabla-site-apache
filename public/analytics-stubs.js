/**
 * Stubs for Vercel Analytics and Speed Insights. Load before their script tags.
 */
window.va =
	window.va ||
	(() => {
		(window.vaq = window.vaq || []).push(arguments);
	});
window.si =
	window.si ||
	(() => {
		(window.siq = window.siq || []).push(arguments);
	});
