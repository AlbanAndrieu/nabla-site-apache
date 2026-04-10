import {
	loadPublicHtmlFragment,
	metadataFromPublicHtml,
} from "@/lib/htmlFromPublic";
import type { Metadata } from "next";

export async function generateMetadata(): Promise<Metadata> {
	const base = await metadataFromPublicHtml("checkout.html", "/checkout");
	return {
		...base,
		robots: { index: false, follow: false },
	};
}

export default async function CheckoutPage() {
	const html = await loadPublicHtmlFragment("checkout.html", "mainOuter");

	return (
		<>
			<div id="top" />
			<a href="#main-content" className="skip-to-main">
				Skip to main content
			</a>
			<div
				className="site-content-page"
				dangerouslySetInnerHTML={{ __html: html }}
			/>
		</>
	);
}
