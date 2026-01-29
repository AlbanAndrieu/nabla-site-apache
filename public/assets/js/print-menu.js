/**
 * Combined Print/Export Menu Utility
 * Provides a dropdown menu for print as PDF (default) and export as markdown
 */

/**
 * Toggles the dropdown menu visibility
 */
function togglePrintMenu(event) {
	event.stopPropagation();
	const menu = document.getElementById('print-menu-dropdown');
	const isVisible = menu.style.display === 'block';
	menu.style.display = isVisible ? 'none' : 'block';
}

/**
 * Handles the print as PDF action
 */
function handlePrintPDF() {
	window.print();
	closePrintMenu();
}

/**
 * Handles the export as markdown action
 */
function handleExportMarkdown() {
	downloadAsMarkdown();
	closePrintMenu();
}

/**
 * Closes the dropdown menu
 */
function closePrintMenu() {
	const menu = document.getElementById('print-menu-dropdown');
	if (menu) {
		menu.style.display = 'none';
	}
}

// Close menu when clicking outside
document.addEventListener('click', function (event) {
	const button = document.querySelector('.print-menu-button');
	const menu = document.getElementById('print-menu-dropdown');
	if (menu && button && !button.contains(event.target)) {
		closePrintMenu();
	}
});

// Close menu on escape key
document.addEventListener('keydown', function (event) {
	if (event.key === 'Escape') {
		closePrintMenu();
	}
});
