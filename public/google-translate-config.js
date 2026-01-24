/**
 * Google Translate configuration
 * Shared configuration for all pages to ensure consistency
 */
function _googleTranslateElementInit() {
	new google.translate.TranslateElement(
		{
			pageLanguage: "en",
			// Languages include: English, French, Norwegian, German, Spanish, Italian,
			// Portuguese, Dutch, Swedish, Danish, Finnish, Polish, Czech, Russian,
			// Arabic, Japanese, Chinese (Simplified)
			includedLanguages:
				"en,fr,no,de,es,it,pt,nl,sv,da,fi,pl,cs,ru,ar,ja,zh-CN",
			layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
			autoDisplay: false,
		},
		"google_translate_element",
	);
}
