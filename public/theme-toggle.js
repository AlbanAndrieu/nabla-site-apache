/**
 * Theme Toggle Script - Dark/Light Mode Switcher
 * Handles theme detection, switching, and persistence
 * @file Browser-side script for theme management
 */

/* eslint-env browser */
/* global gtag */

(() => {
	// Theme constants
	const THEMES = {
		LIGHT: "light",
		DARK: "dark",
		AUTO: "auto",
	};

	const STORAGE_KEY = "site-theme-preference";
	const THEME_ICONS = {
		[THEMES.LIGHT]: "☀️",
		[THEMES.DARK]: "🌙",
		[THEMES.AUTO]: "🌓",
	};

	/**
	 * Get user's preferred color scheme from browser
	 */
	function getSystemPreference() {
		if (window.matchMedia?.("(prefers-color-scheme: dark)").matches) {
			return THEMES.DARK;
		}
		return THEMES.LIGHT;
	}

	/**
	 * Get stored theme preference or default to auto
	 */
	function getStoredTheme() {
		try {
			const stored = localStorage.getItem(STORAGE_KEY);
			return stored && Object.values(THEMES).includes(stored)
				? stored
				: THEMES.AUTO;
		} catch (e) {
			console.warn("Unable to access localStorage:", e);
			return THEMES.AUTO;
		}
	}

	/**
	 * Store theme preference
	 */
	function storeTheme(theme) {
		try {
			localStorage.setItem(STORAGE_KEY, theme);
		} catch (e) {
			console.warn("Unable to store theme preference:", e);
		}
	}

	/**
	 * Apply theme to document
	 */
	function applyTheme(theme) {
		const effectiveTheme =
			theme === THEMES.AUTO ? getSystemPreference() : theme;

		// Set data attribute on html element
		document.documentElement.setAttribute("data-theme", effectiveTheme);

		// Update button icon if it exists
		const button = document.getElementById("theme-toggle-btn");
		if (button) {
			const nextTheme = getNextTheme(theme);
			button.textContent = THEME_ICONS[nextTheme];
			button.setAttribute(
				"aria-label",
				`Switch to ${nextTheme} theme (current: ${theme})`,
			);
			button.setAttribute("title", `Switch to ${nextTheme} theme`);
		}

		// Dispatch event for other scripts to react to theme changes
		window.dispatchEvent(
			new CustomEvent("themechange", {
				detail: { theme: effectiveTheme, preference: theme },
			}),
		);
	}

	/**
	 * Get next theme in rotation: light -> dark -> auto -> light
	 */
	function getNextTheme(currentTheme) {
		switch (currentTheme) {
			case THEMES.LIGHT:
				return THEMES.DARK;
			case THEMES.DARK:
				return THEMES.AUTO;
			default:
				return THEMES.LIGHT;
		}
	}

	/**
	 * Toggle theme
	 */
	function toggleTheme() {
		const currentTheme = getStoredTheme();
		const nextTheme = getNextTheme(currentTheme);
		storeTheme(nextTheme);
		applyTheme(nextTheme);

		// Analytics tracking (if available)
		if (typeof gtag === "function") {
			gtag("event", "theme_toggle", {
				event_category: "UI",
				event_label: nextTheme,
			});
		}
	}

	/**
	 * Create and inject theme toggle button
	 */
	function createThemeToggle() {
		// Check if button already exists
		if (document.getElementById("theme-toggle-btn")) {
			return;
		}

		const currentTheme = getStoredTheme();
		const nextTheme = getNextTheme(currentTheme);

		const button = document.createElement("button");
		button.id = "theme-toggle-btn";
		button.className = "theme-toggle";
		button.textContent = THEME_ICONS[nextTheme];
		button.setAttribute(
			"aria-label",
			`Switch to ${nextTheme} theme (current: ${currentTheme})`,
		);
		button.setAttribute("title", `Switch to ${nextTheme} theme`);
		button.setAttribute("type", "button");

		button.addEventListener("click", toggleTheme);

		// Add keyboard support
		button.addEventListener("keydown", (e) => {
			if (e.key === "Enter" || e.key === " ") {
				e.preventDefault();
				toggleTheme();
			}
		});

		document.body.appendChild(button);
	}

	/**
	 * Listen for system theme changes
	 */
	function watchSystemTheme() {
		if (!window.matchMedia) return;

		const darkModeQuery = window.matchMedia("(prefers-color-scheme: dark)");

		// Modern browsers
		if (darkModeQuery.addEventListener) {
			darkModeQuery.addEventListener("change", () => {
				const storedTheme = getStoredTheme();
				if (storedTheme === THEMES.AUTO) {
					applyTheme(THEMES.AUTO);
				}
			});
		}
		// Older browsers
		else if (darkModeQuery.addListener) {
			darkModeQuery.addListener(() => {
				const storedTheme = getStoredTheme();
				if (storedTheme === THEMES.AUTO) {
					applyTheme(THEMES.AUTO);
				}
			});
		}
	}

	/**
	 * Initialize theme system
	 */
	function initTheme() {
		// Apply theme as early as possible to prevent flash
		const storedTheme = getStoredTheme();
		applyTheme(storedTheme);

		// Wait for DOM to be ready before creating button
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", () => {
				createThemeToggle();
				watchSystemTheme();
			});
		} else {
			createThemeToggle();
			watchSystemTheme();
		}
	}

	// Initialize immediately
	initTheme();

	// Expose API for programmatic control
	window.themeToggle = {
		toggle: toggleTheme,
		set: (theme) => {
			if (Object.values(THEMES).includes(theme)) {
				storeTheme(theme);
				applyTheme(theme);
			}
		},
		get: getStoredTheme,
		getEffective: () => {
			const theme = getStoredTheme();
			return theme === THEMES.AUTO ? getSystemPreference() : theme;
		},
	};
})();
