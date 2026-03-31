/**
 * Hosted Stripe Checkout: POST /create-checkout-session, then redirect to session.url.
 * Expects JSON { url } when Accept: application/json (see server.cjs).
 */
(() => {
	const form = document.querySelector(
		"form.checkout-form[data-stripe-checkout]",
	);
	if (!form) return;

	const button = form.querySelector("#checkout-button, button[type='submit']");
	const errorEl = document.getElementById("checkout-error");
	const endpoint =
		form.getAttribute("data-checkout-endpoint") || "/create-checkout-session";

	function setError(message) {
		if (!errorEl) return;
		errorEl.textContent = message;
		errorEl.hidden = false;
	}

	function clearError() {
		if (!errorEl) return;
		errorEl.textContent = "";
		errorEl.hidden = true;
	}

	form.addEventListener("submit", async (e) => {
		e.preventDefault();
		clearError();

		if (button) {
			button.disabled = true;
			button.setAttribute("aria-busy", "true");
		}

		try {
			const response = await fetch(endpoint, {
				method: "POST",
				headers: {
					Accept: "application/json",
					"Content-Type": "application/json",
				},
				body: "{}",
			});

			let data = {};
			const text = await response.text();
			try {
				data = text ? JSON.parse(text) : {};
			} catch {
				data = { error: text || response.statusText };
			}

			if (!response.ok) {
				throw new Error(
					data.error || data.message || "Could not start checkout.",
				);
			}
			if (!data.url) {
				throw new Error("Invalid response from server (no checkout URL).");
			}

			window.location.href = data.url;
		} catch (err) {
			setError(err.message || "Something went wrong. Please try again.");
			if (button) {
				button.disabled = false;
				button.removeAttribute("aria-busy");
			}
		}
	});
})();
