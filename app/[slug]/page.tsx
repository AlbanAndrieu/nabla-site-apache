import {
	loadPublicHtmlFragment,
	metadataFromPublicHtml,
} from "@/lib/htmlFromPublic";
import { MARKETING_PAGES } from "@/lib/marketingPages";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

type Props = { params: Promise<{ slug: string }> };

export async function generateStaticParams() {
	return Object.keys(MARKETING_PAGES).map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
	const { slug } = await params;
	const spec = MARKETING_PAGES[slug];
	if (!spec) return {};
	return metadataFromPublicHtml(spec.file, `/${slug}`);
}

export default async function MarketingSlugPage({ params }: Props) {
	const { slug } = await params;
	const spec = MARKETING_PAGES[slug];
	if (!spec) notFound();

	const html = await loadPublicHtmlFragment(spec.file, spec.mode);

	return (
		<>
			<a name="top" />
			<a href="#main-content" className="skip-to-main">
				Skip to main content
			</a>
			<div
				className={spec.bodyClass}
				dangerouslySetInnerHTML={{ __html: html }}
			/>
		</>
	);
}
