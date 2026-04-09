"use server";

import { stripe } from "@/lib/stripe";
import { headers } from "next/headers";

export async function startCheckoutSession(productId: string) {
	// Implement your product catalog lookup.
	const product = await getProduct(productId);

	// Create Checkout Sessions from body params.
	const session = await stripe.checkout.sessions.create({
		ui_mode: "embedded",
		redirect_on_completion: "never",
		line_items: [
			{
				price_data: {
					currency: "usd",
					product_data: {
						name: product.name,
						description: product.description,
					},
					unit_amount: product.priceInCents,
				},
				quantity: 1,
			},
		],
		mode: "payment",
	});

	return session.client_secret;
}
