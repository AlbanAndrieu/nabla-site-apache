/**
 * Smooth scrolling for anchor links and optional scroll-triggered animations.
 * Use class .js-animate-on-scroll or data-animate-on-scroll for elements to animate when in view.
 */
(() => {
	// Smooth scrolling for anchor links
	document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
		anchor.addEventListener("click", function (e) {
			var href = this.getAttribute("href");
			if (href === "#" || href.length <= 1) return;
			e.preventDefault();
			var target = document.querySelector(href);
			if (target) {
				target.scrollIntoView({ behavior: "smooth", block: "start" });
			}
		});
	});

	// Optional: animate elements when they enter the viewport
	var observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
	var observer = new IntersectionObserver((entries) => {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				entry.target.style.opacity = "1";
				entry.target.style.transform = "translateY(0)";
			}
		});
	}, observerOptions);

	var selectors =
		".service-card, .skill-category, .tool-item, .contact-card, .social-card, .js-animate-on-scroll, [data-animate-on-scroll]";
	document.querySelectorAll(selectors).forEach((el) => {
		el.style.opacity = "0";
		el.style.transform = "translateY(20px)";
		el.style.transition = "opacity 0.6s ease, transform 0.6s ease";
		observer.observe(el);
	});
})();
