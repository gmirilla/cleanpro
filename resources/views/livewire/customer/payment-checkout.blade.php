<div>
    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Invoice header --}}
            <div class="bg-indigo-600 p-6 text-white">
                <p class="text-indigo-200 text-sm mb-1">Invoice</p>
                <h2 class="text-2xl font-bold">{{ $invoice->invoice_number }}</h2>
                <p class="text-indigo-200 text-sm mt-1">
                    Booking: {{ $invoice->booking->booking_reference }}
                </p>
            </div>

            <div class="p-6">

                {{-- Line items --}}
                <div class="space-y-2 mb-4 text-sm">
                    @foreach($invoice->booking->items as $item)
                        <div class="flex justify-between text-gray-600">
                            <span>
                                {{ $item->service->name }}
                                @if($item->quantity > 1)
                                    <span class="text-gray-400">× {{ $item->quantity }}</span>
                                @endif
                            </span>
                            <span>₦{{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach

                    <div class="flex justify-between text-gray-500 border-t border-gray-100 pt-2">
                        <span>VAT (7.5%)</span>
                        <span>₦{{ number_format($invoice->tax, 2) }}</span>
                    </div>

                    @if($invoice->discount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Discount</span>
                            <span>− ₦{{ number_format($invoice->discount, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between font-bold text-gray-800 text-lg border-t border-gray-200 pt-3 mt-2">
                        <span>Total Due</span>
                        <span>₦{{ number_format($invoice->total, 2) }}</span>
                    </div>
                </div>

                {{-- Due date --}}
                <p class="text-xs text-gray-400 mb-5">
                    Due: <span class="{{ $invoice->isOverdue() ? 'text-red-500 font-medium' : 'text-gray-600' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                        @if($invoice->isOverdue()) (Overdue) @endif
                    </span>
                </p>

                @if($paid)
                    {{-- Success state --}}
                    <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                        <p class="text-4xl mb-2">✅</p>
                        <p class="font-bold text-green-700 text-lg">Payment Successful!</p>
                        <p class="text-sm text-green-600 mt-1">Your booking is now confirmed.</p>
                        <a href="{{ route('customer.bookings') }}"
                           class="mt-4 inline-block bg-green-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                            View My Bookings
                        </a>
                    </div>

                @else
                    {{-- Gateway selector --}}
                    <div class="mb-5">
                        <p class="text-sm font-medium text-gray-700 mb-3">Choose Payment Method</p>
                        <div class="grid grid-cols-2 gap-3">
                            <button wire:click="$set('gateway','paystack')" type="button"
                                    class="border-2 rounded-xl p-3 text-center text-sm font-medium transition
                                        {{ $gateway === 'paystack'
                                            ? 'border-green-500 bg-green-50 text-green-700'
                                            : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                                💳 Paystack
                            </button>
                            <button wire:click="$set('gateway','stripe')" type="button"
                                    class="border-2 rounded-xl p-3 text-center text-sm font-medium transition
                                        {{ $gateway === 'stripe'
                                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                            : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                                💳 Stripe
                            </button>
                        </div>
                    </div>

                    {{-- Paystack button --}}
                    @if($gateway === 'paystack')
                        <button wire:click="payWithPaystack"
                                wire:loading.attr="disabled"
                                type="button"
                                class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white py-3 rounded-xl font-semibold text-sm transition">
                            <span wire:loading.remove wire:target="payWithPaystack">
                                Pay ₦{{ number_format($invoice->total, 2) }} with Paystack
                            </span>
                            <span wire:loading wire:target="payWithPaystack">
                                Redirecting to Paystack…
                            </span>
                        </button>
                        <p class="text-xs text-gray-400 text-center mt-2">
                            You will be redirected to Paystack's secure checkout page.
                        </p>
                    @endif

                    {{-- Stripe --}}
                    @if($gateway === 'stripe')
                        @if(!$clientSecret)
                            <button wire:click="initializeStripe"
                                    wire:loading.attr="disabled"
                                    type="button"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white py-3 rounded-xl font-semibold text-sm transition">
                                <span wire:loading.remove wire:target="initializeStripe">Continue to Card Payment</span>
                                <span wire:loading wire:target="initializeStripe">Loading…</span>
                            </button>
                        @else
                            <div id="stripe-payment-element" class="mb-4 p-3 border border-gray-200 rounded-xl"></div>
                            <div id="stripe-errors" class="text-red-500 text-sm mb-3 hidden"></div>
                            <button id="stripe-pay-btn" type="button"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition">
                                Pay ₦{{ number_format($invoice->total, 2) }}
                            </button>
                            <script src="https://js.stripe.com/v3/"></script>
                            <script>
                                (function() {
                                    const stripe   = Stripe('{{ config('services.stripe.key') }}');
                                    const elements = stripe.elements({ clientSecret: '{{ $clientSecret }}' });
                                    const payEl    = elements.create('payment');
                                    payEl.mount('#stripe-payment-element');

                                    document.getElementById('stripe-pay-btn').addEventListener('click', async () => {
                                        const btn = document.getElementById('stripe-pay-btn');
                                        btn.disabled = true;
                                        btn.textContent = 'Processing…';

                                        const { error } = await stripe.confirmPayment({
                                            elements,
                                            confirmParams: {
                                                return_url: '{{ route('customer.dashboard') }}'
                                            }
                                        });

                                        if (error) {
                                            const errEl = document.getElementById('stripe-errors');
                                            errEl.textContent = error.message;
                                            errEl.classList.remove('hidden');
                                            btn.disabled = false;
                                            btn.textContent = 'Try Again';
                                        }
                                    });
                                })();
                            </script>
                        @endif
                    @endif
                @endif

                <div class="mt-5 text-center">
                    <a href="{{ route('customer.invoices') }}"
                       class="text-sm text-gray-400 hover:text-gray-600 transition">
                        ← Back to Invoices
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
