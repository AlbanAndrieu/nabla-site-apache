import type { NextConfig } from "next";
import createNextIntlPlugin from "next-intl/plugin";

const withNextIntl = createNextIntlPlugin("./i18n/request.ts");

const localePrefixes = ["en", "fr"] as const;

const policyRewrites = [
	"legal",
	"impressum",
	"privacy_policy",
	"service_terms",
	"cookie_policy",
	"accessibility_statement",
].map((name) => ({
	source: `/policy/${name}`,
	destination: `/policy/${name}.html`,
}));

const localizedPolicyRewrites = localePrefixes.flatMap((locale) =>
	policyRewrites.map((rewrite) => ({
		source: `/${locale}${rewrite.source}`,
		destination: rewrite.destination,
	})),
);

const nextConfig: NextConfig = {
	reactStrictMode: true,
	async rewrites() {
		return [...localizedPolicyRewrites, ...policyRewrites];
	},
};

export default withNextIntl(nextConfig);
