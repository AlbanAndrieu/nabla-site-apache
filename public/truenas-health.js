/**
 * TrueNAS services grid: show per-card tunnel reachability (best-effort from the visitor's browser).
 * Uses fetch(..., { mode: 'no-cors' }) so cross-origin tunnels can be probed without CORS headers;
 * green = request completed, red = network/timeout/abort. TCP-only (postgres://) is shown as gray.
 */
(function () {
	'use strict';

	var SECTION = document.querySelector('.truenas-page-apps');
	if (!SECTION) return;

	var PROBE_MS = 10000;
	var STAGGER_MS = 150;

	function sleep(ms) {
		return new Promise(function (resolve) {
			setTimeout(resolve, ms);
		});
	}

	function probeUrl(url) {
		var ac = new AbortController();
		var timer = setTimeout(function () {
			ac.abort();
		}, PROBE_MS);
		return fetch(url, {
			method: 'GET',
			mode: 'no-cors',
			cache: 'no-store',
			signal: ac.signal,
		})
			.then(function () {
				clearTimeout(timer);
				return true;
			})
			.catch(function () {
				clearTimeout(timer);
				return false;
			});
	}

	function setIndicator(el, state, url) {
		el.textContent = '';
		var i = document.createElement('i');
		i.setAttribute('aria-hidden', 'true');
		if (state === 'pending') {
			i.className = 'fas fa-circle-notch fa-spin truenas-health-indicator__icon truenas-health-indicator__icon--pending';
			el.setAttribute('aria-label', 'Checking tunnel reachability');
			el.setAttribute('title', 'Checking whether the tunnel responds from your browser…');
		} else if (state === 'up') {
			i.className = 'fas fa-circle truenas-health-indicator__icon truenas-health-indicator__icon--up';
			el.setAttribute('aria-label', 'Tunnel appears reachable');
			el.setAttribute('title', 'Tunnel responded (browser check): ' + url);
		} else if (state === 'down') {
			i.className = 'fas fa-circle truenas-health-indicator__icon truenas-health-indicator__icon--down';
			el.setAttribute('aria-label', 'Tunnel unreachable from this browser');
			el.setAttribute('title', 'No response or error when checking: ' + url);
		} else if (state === 'na') {
			i.className = 'fas fa-minus truenas-health-indicator__icon truenas-health-indicator__icon--na';
			el.setAttribute('aria-label', 'Not checked: TCP tunnel only');
			el.setAttribute(
				'title',
				'PostgreSQL uses a TCP tunnel; reachability is not tested in the browser.',
			);
		}
		el.appendChild(i);
	}

	function mountTitleRow(titleEl) {
		if (titleEl.querySelector('.truenas-health-indicator')) return null;
		titleEl.classList.add(
			'd-flex',
			'flex-wrap',
			'align-items-center',
			'justify-content-between',
			'gap-2',
		);
		var textWrap = document.createElement('span');
		textWrap.className = 'truenas-card-title-text';
		while (titleEl.firstChild) {
			textWrap.appendChild(titleEl.firstChild);
		}
		var ind = document.createElement('span');
		ind.className = 'truenas-health-indicator flex-shrink-0';
		ind.setAttribute('role', 'img');
		titleEl.appendChild(textWrap);
		titleEl.appendChild(ind);
		return ind;
	}

	var actionBlocks = SECTION.querySelectorAll('.truenas-app-actions');
	var jobs = [];

	actionBlocks.forEach(function (actions) {
		var cardBody = actions.closest('.card-body');
		if (!cardBody) return;
		var titleEl = cardBody.querySelector(':scope > .card-title');
		if (!titleEl) return;
		var tunnelA = actions.querySelector('a.btn-outline-primary');
		if (!tunnelA) return;
		var href = (tunnelA.getAttribute('href') || '').trim();
		var ind = mountTitleRow(titleEl);
		if (!ind) return;

		if (href.indexOf('postgres:') === 0) {
			setIndicator(ind, 'na', href);
			return;
		}
		if (href.indexOf('https://') !== 0) {
			setIndicator(ind, 'na', href);
			return;
		}

		setIndicator(ind, 'pending', href);
		jobs.push({ el: ind, url: href });
	});

	if (!jobs.length) return;

	(async function runSequentially() {
		for (var j = 0; j < jobs.length; j += 1) {
			var job = jobs[j];
			var ok = await probeUrl(job.url);
			setIndicator(job.el, ok ? 'up' : 'down', job.url);
			await sleep(STAGGER_MS);
		}
	})();
})();
