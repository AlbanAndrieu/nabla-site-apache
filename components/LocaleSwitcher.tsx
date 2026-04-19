"use client";

import { usePathname, useRouter } from "@/i18n/navigation";
import { useLocale } from "next-intl";
import { useTranslations } from "next-intl";
import { useParams } from "next/navigation";
import type { ChangeEvent } from "react";

export default function LocaleSwitcher() {
	const locale = useLocale();
	const t = useTranslations("site");
	const pathname = usePathname();
	const router = useRouter();
	const params = useParams();

	function handleLocaleChange(event: ChangeEvent<HTMLSelectElement>) {
		const nextLocale = event.target.value as "en" | "fr";
		router.replace(
			{
				pathname,
				// `params` keeps dynamic segments when switching locale.
				params: params as Record<string, string>,
			},
			{ locale: nextLocale },
		);
	}

	return (
		<label
			htmlFor="locale-switcher"
			className="d-inline-flex align-items-center gap-2 text-light small"
		>
			<span>{t("localeSwitcherLabel")}</span>
			<select
				id="locale-switcher"
				name="locale"
				value={locale}
				onChange={handleLocaleChange}
				className="form-select form-select-sm"
				aria-label={t("switchLanguage")}
			>
				<option value="en">{t("languageName.en")}</option>
				<option value="fr">{t("languageName.fr")}</option>
			</select>
		</label>
	);
}
