import type { NextConfig } from "next";

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

const nextConfig: NextConfig = {
	reactStrictMode: true,
	async rewrites() {
		return policyRewrites;
	},
};

export default nextConfig;
