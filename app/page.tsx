import { loadPublicHtmlFragment } from "@/lib/htmlFromPublic";
import type { Metadata } from "next";
import Script from "next/script";

const HOME_JSON_LD = {
	"@context": "https://schema.org",
	"@type": "Person",
	name: "Alban Andrieu",
	jobTitle: "Freelance DevSecOps Engineer & Cloud Architect",
	description:
		"Freelance DevSecOps engineer and cloud architect (AWS, Azure, OVH). Cloud security consultant for AI-driven and security-critical products; ISO 27001, SOC 2, GDPR-aligned delivery.",
	url: "https://dr-alban.com/",
	email: "job@dr-alban.com",
	sameAs: [
		"https://www.linkedin.com/in/nabla/",
		"https://twitter.com/AlbanAndrieu",
		"https://github.com/AlbanAndrieu",
	],
	knowsAbout: [
		"Freelance DevSecOps",
		"Cloud architecture",
		"AWS",
		"Azure",
		"OVHcloud",
		"Cloud security",
		"AI infrastructure",
		"MLOps",
		"ISO 27001",
		"SOC 2",
	],
	hasCredential: [
		{
			"@type": "EducationalOccupationalCredential",
			name: "LinkedIn Professional Profile",
			url: "https://www.linkedin.com/in/nabla/",
		},
	],
	subjectOf: [
		{
			"@type": "DigitalDocument",
			name: "LaTeX Resume PDF",
			description: "Traditional formatted resume in PDF format",
			url: "https://dr-alban.com/cv/cv-aandrieu-2026.pdf",
			encodingFormat: "application/pdf",
		},
		{
			"@type": "DigitalDocument",
			name: "LinkedIn Resume PDF",
			description: "LinkedIn profile exported as PDF",
			url: "https://dr-alban.com/cv/linkedin/cv-aandrieu-linkedin-2026-01-01.pdf",
			encodingFormat: "application/pdf",
		},
		{
			"@type": "WebPage",
			name: "Online CV Landing Page",
			description: "Interactive web-based CV and professional profile",
			url: "https://dr-alban.com/cv",
		},
	],
};

export async function generateMetadata(): Promise<Metadata> {
	return {
		title:
			"Alban Andrieu — Freelance DevSecOps & Cloud Architect (AWS, Azure, OVH)",
		description:
			"Freelance DevSecOps engineer and cloud architect with 20+ years of experience. I help startups and enterprises secure, automate and scale AWS, Azure and OVH platforms, with a focus on AI workloads, security and compliance (ISO 27001 / SOC 2).",
		keywords: [
			"freelance DevSecOps engineer",
			"freelance cloud architect",
			"AWS",
			"Azure",
			"OVH",
			"DevSecOps consultant",
			"cloud security consultant",
			"DevSecOps for AI startups",
			"Alban Andrieu",
		],
		authors: [{ name: "Alban Andrieu" }],
		openGraph: {
			type: "profile",
			url: "https://dr-alban.com/",
			title: "Alban Andrieu — Freelance DevSecOps & Cloud Architect",
			description:
				"Freelance DevSecOps and cloud architect. Secure, automate and scale AWS, Azure and OVH platforms for AI-driven and security-critical products.",
			images: [{ url: "https://dr-alban.com/assets/nabla/nabla-4.svg" }],
		},
		twitter: {
			card: "summary",
			title: "Alban Andrieu — Freelance DevSecOps & Cloud Architect",
			description:
				"Freelance cloud security consultant and DevSecOps engineer. AWS, Azure, OVH — AI infra, compliance, CI/CD and IaC.",
			images: ["https://dr-alban.com/assets/nabla/nabla-4.svg"],
		},
		alternates: {
			canonical: "https://dr-alban.com/",
			languages: { "x-default": "https://dr-alban.com/" },
		},
	};
}

export default async function HomePage() {
	const inner = await loadPublicHtmlFragment("index.html", "main");

	return (
		<div className="home-page">
			<script
				type="application/ld+json"
				// eslint-disable-next-line react/no-danger
				dangerouslySetInnerHTML={{
					__html: JSON.stringify(HOME_JSON_LD),
				}}
			/>
			<Script src="/obs-connection.js" strategy="lazyOnload" />
			<Script
				src="https://challenges.cloudflare.com/turnstile/v0/api.js"
				strategy="lazyOnload"
			/>
			<Script
				src="/site-analytics.js"
				strategy="afterInteractive"
				data-analytics-mode="home"
				data-ahrefs-key="tg3zLMS/bebJFl0LxctiCw"
			/>
			<Script
				src="https://uptime.betterstack.com/widgets/announcement.js"
				strategy="lazyOnload"
				data-id="150620"
			/>
			<a name="top" />
			<a href="#main-content" className="skip-to-main">
				Skip to main content
			</a>
			<main
				id="main-content"
				role="main"
				dangerouslySetInnerHTML={{ __html: inner }}
			/>
		</div>
	);
}
