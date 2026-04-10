/**
 * nabla.html — optional reachability hint per HTTP(S) service link.
 * Uses a favicon image probe (no CORS read); false negatives are possible (no favicon, CSP, LAN-only URLs).
 */
(function () {
	"use strict";

	var CONCURRENCY = 5;
	var IMAGE_TIMEOUT_MS = 6500;

	function probeImage(src) {
		return new Promise(function (resolve) {
			var img = new Image();
			var t = window.setTimeout(function () {
				img.onload = null;
				img.onerror = null;
				resolve(false);
			}, IMAGE_TIMEOUT_MS);
			img.onload = function () {
				window.clearTimeout(t);
				resolve(true);
			};
			img.onerror = function () {
				window.clearTimeout(t);
				resolve(false);
			};
			img.src = src + (src.indexOf("?") === -1 ? "?" : "&") + "_np=" + Date.now();
		});
	}

	function probeOrigin(origin) {
		var paths = ["/favicon.ico", "/favicon.png", "/apple-touch-icon.png"];
		var i = 0;
		var base = origin.replace(/\/$/, "");
		function attempt() {
			if (i >= paths.length) {
				return Promise.resolve(false);
			}
			var url = base + paths[i++];
			return probeImage(url).then(function (ok) {
				return ok || attempt();
			});
		}
		return attempt();
	}

	function makePing() {
		var span = document.createElement("span");
		span.className = "nabla-svc-ping nabla-svc-ping--pending";
		span.setAttribute("role", "img");
		span.setAttribute("aria-label", "Checking reachability");
		span.title = "Probing host via favicon; may be wrong if the app has no favicon or blocks hotlinking.";
		return span;
	}

	function setPingState(el, state) {
		el.classList.remove("nabla-svc-ping--pending", "nabla-svc-ping--ok", "nabla-svc-ping--fail", "nabla-svc-ping--unknown");
		if (state === "ok") {
			el.classList.add("nabla-svc-ping--ok");
			el.setAttribute("aria-label", "Host responded (favicon probe)");
			el.title = "Probe succeeded (favicon loaded). The app may still require auth.";
		} else if (state === "fail") {
			el.classList.add("nabla-svc-ping--fail");
			el.setAttribute("aria-label", "Unreachable from this browser or no favicon");
			el.title = "Probe failed: offline, blocked, no favicon, or not reachable from your network (e.g. LAN-only).";
		} else {
			el.classList.add("nabla-svc-ping--unknown");
			el.setAttribute("aria-label", "Not probed");
			el.title = "This URL is not probed from the page (e.g. postgres://).";
		}
	}

	function collectJobs() {
		var jobs = [];
		var seen = Object.create(null);

		function addAnchor(a) {
			var href = a.getAttribute("href");
			if (!href || href.charAt(0) === "#" || href.indexOf("javascript:") === 0) {
				return;
			}
			if (/^mailto:/i.test(href)) {
				return;
			}
			if (/^postgres:/i.test(href)) {
				var pingU = makePing();
				if (a.classList.contains("btn")) {
					a.appendChild(document.createTextNode(" "));
					a.appendChild(pingU);
				} else {
					a.insertAdjacentElement("afterend", pingU);
				}
				setPingState(pingU, "unknown");
				return;
			}
			var url;
			try {
				url = new URL(href, window.location.href);
			} catch {
				return;
			}
			if (url.protocol !== "http:" && url.protocol !== "https:") {
				return;
			}
			var origin = url.origin;
			var ping = makePing();
			if (a.classList.contains("btn")) {
				a.appendChild(document.createTextNode(" "));
				a.appendChild(ping);
			} else {
				a.insertAdjacentElement("afterend", ping);
			}
			if (!seen[origin]) {
				seen[origin] = [];
			}
			seen[origin].push(ping);
		}

		document.querySelectorAll(".nabla-platforms-section a.nabla-tool-tag-link[href]").forEach(addAnchor);
		document.querySelectorAll(".nabla-homelab-services .nabla-homelab-card a.btn[href]").forEach(addAnchor);
		document.querySelectorAll("#services a.opensource-link[href], .opensource-section a.opensource-link[href]").forEach(
			addAnchor
		);

		Object.keys(seen).forEach(function (origin) {
			jobs.push({ origin: origin, pings: seen[origin] });
		});
		return jobs;
	}

	function runQueue(jobs) {
		var queue = jobs.slice();
		function worker() {
			function runNext() {
				var job = queue.shift();
				if (!job) {
					return Promise.resolve();
				}
				return probeOrigin(job.origin).then(function (ok) {
					var state = ok ? "ok" : "fail";
					job.pings.forEach(function (p) {
						setPingState(p, state);
					});
					return runNext();
				});
			}
			return runNext();
		}
		var workers = [];
		for (var w = 0; w < CONCURRENCY; w++) {
			workers.push(worker());
		}
		return Promise.all(workers);
	}

	function init() {
		if (!document.body.classList.contains("page-nabla-best-practices")) {
			return;
		}
		var jobs = collectJobs();
		if (!jobs.length) {
			return;
		}
		runQueue(jobs).catch(function () {});
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();
