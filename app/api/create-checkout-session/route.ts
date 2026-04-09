import { type NextRequest, NextResponse } from "next/server";
import Stripe from "stripe";

export const runtime = "nodejs";

function getOrigin(req: NextRequest): string {
	const configured = (process.env.DOMAIN || "").replace(/\/$/, "");
	if (configured) return configured;
	const proto =
		req.headers.get("x-forwarded-proto")?.split(",")[0].trim() || "https";
	const host =
		req.headers.get("x-forwarded-host")?.split(",")[0].trim() ||
		req.headers.get("host")?.split(",")[0].trim();
	if (!host) return "https://www.dr-alban.com";
	return `${proto}://${host}`;
}

function wantsJson(req: NextRequest): boolean {
	const accept = req.headers.get("accept") || "";
	return accept.includes("application/json");
}

export async function POST(req: NextRequest) {
	const stripeSecret =
		process.env.STRIPE_SECRET_KEY || process.env.STRIPE_KEY || "";
	const priceId = process.env.STRIPE_PRICE_ID || process.env.PRICE_ID || "";

	if (!stripeSecret) {
		const msg =
			"Missing STRIPE_SECRET_KEY. Set it in the Vercel project environment.";
		return wantsJson(req)
			? NextResponse.json({ error: msg }, { status: 500 })
			: new NextResponse(msg, { status: 500 });
	}
	if (!priceId) {
		const msg =
			"Missing STRIPE_PRICE_ID. Create a Price in Stripe and set STRIPE_PRICE_ID.";
		return wantsJson(req)
			? NextResponse.json({ error: msg }, { status: 500 })
			: new NextResponse(msg, { status: 500 });
	}

	try {
		await req.json();
	} catch {
		// Form POST or empty body
	}

	const origin = getOrigin(req);
	const stripe = new Stripe(stripeSecret);

	try {
		const session = await stripe.checkout.sessions.create({
			line_items: [{ price: priceId, quantity: 1 }],
			mode: "payment",
			success_url: `${origin}/success?session_id={CHECKOUT_SESSION_ID}`,
			cancel_url: `${origin}/cancel`,
		});

		if (!session.url) {
			const msg = "Checkout session missing redirect URL.";
			return wantsJson(req)
				? NextResponse.json({ error: msg }, { status: 500 })
				: new NextResponse(msg, { status: 500 });
		}

		if (wantsJson(req)) {
			return NextResponse.json({ url: session.url });
		}
		return NextResponse.redirect(session.url, 303);
	} catch (err: unknown) {
		console.error("create-checkout-session:", err);
		const message =
			err instanceof Error ? err.message : "Checkout session failed.";
		return wantsJson(req)
			? NextResponse.json({ error: message }, { status: 500 })
			: new NextResponse(message, { status: 500 });
	}
}
