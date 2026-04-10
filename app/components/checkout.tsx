'use client'

import { startCheckoutSession } from '@/app/actions/stripe'
import {
  EmbeddedCheckout,
  EmbeddedCheckoutProvider
} from '@stripe/react-stripe-js'
import { loadStripe } from '@stripe/stripe-js'
import { useCallback, useMemo } from 'react'

const publishableKey =
  process.env.NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY?.trim() ?? ''

export default function Checkout({ productId }: { productId: string }) {
  const stripePromise = useMemo(
    () => (publishableKey ? loadStripe(publishableKey) : null),
    []
  )

  const fetchClientSecret = useCallback(async () => {
    const clientSecret = await startCheckoutSession(productId)
    if (!clientSecret) {
      throw new Error('Could not create checkout session.')
    }
    return clientSecret
  }, [productId])

  if (!stripePromise) {
    return (
      <div id="checkout" role="alert">
        Checkout is unavailable: set NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY.
      </div>
    )
  }

  return (
    <div id="checkout">
      <EmbeddedCheckoutProvider
        stripe={stripePromise}
        options={{ fetchClientSecret }}
      >
        <EmbeddedCheckout />
      </EmbeddedCheckoutProvider>
    </div>
  )
}
