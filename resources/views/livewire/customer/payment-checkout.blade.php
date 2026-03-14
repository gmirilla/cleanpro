<div>
    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Invoice Summary --}}
            <div class="bg-indigo-600 p-6 text-white">
                <p class="text-sm text-indigo-200 mb-1">Invoice</p>
                <h2 class="text-2xl font-bold">{{ $invoice->invoice_number }}</h2>
                <p class="text-indigo-200 text-sm mt-1">{{ $invoice->booking->booking_reference }}</p>
            </div>

            <div class="p-6">
                {{-- Line items --}}
                <div class="space-y-2 mb-4 text-sm">
                    @foreach($invoice->booking->items as $item)
                        <div class="flex justify-between text-gray-600">
                            <span>{{ $item->service->name }} × {{ $item->quantity }}</span>
                            <span>₦{{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-gray-500 border-t border-gray-100 pt-2">
                        <span>Tax (7.5%)</span>
                        <span>₦{{ number_format($invoice->tax, 2) }}</span>
                    </div>
                    @if($invoice->discount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Discount</span>
                            <span>- ₦{{ number_format($invoice->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-gray-800 text-lg border-t border-gray-200 pt-3 mt-2">
                        <span>Total Due</span>
                        <span>₦{{ number_format($invoice->total, 2) }}</span>
                    </div>
                </div>

                @if($paid)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
                        <p class="text-4xl mb-2">✅</p>
                        <p class="font-bold text-green-700">Payment Successful!</p>
                        <p class="text-sm text-green-600 mt-1">Your booking is now confirmed.</p>
                        <a href="{{ route('customer.bookings') }}"
                           class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                            View Booking
                        </a>
                    </div>
                @else
                    {{-- Gateway selector --}}
                    <div class="mb-5">
                        <p class="text-sm font-medium text-gray-700 mb-3">Payment Method</p>
                        <div class="grid grid-cols-2 gap-3">
                            <button wire:click="$set('gateway','paystack')"
                                    class="border-2 rounded-xl p-3 text-center text-sm font-medium transition
                                        {{ $gateway === 'paystack' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                💳 Paystack
                            </button>
                            <button wire:click="$set('gateway','stripe')"
                                    class="border-2 rounded-xl p-3 text-center text-sm font-medium transition
                                        {{ $gateway === 'stripe' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                💳 Stripe
                            </button>
                        </div>
                    </div>

                    {{-- Paystack --}}
                    @if($gateway === 'paystack')
                        <button wire:click="payWithPaystack"
                                wire:loading.attr="disabled"
                                class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white py-3 rounded-xl font-semibold text-sm transition">
                            <span wire:loading.remove wire:target="payWithPaystack">
                                Pay ₦{{ number_format($invoice->total, 2) }} with Paystack
                            </span>
                            <span wire:loading wire:target="payWithPaystack">Redirecting…</span>
                        </button>
                        <p class="text-xs text-gray-400 text-center mt-2">You will be redirected to Paystack's secure payment page.</p>
                    @endif

                    {{-- Stripe --}}
                    @if($gateway === 'stripe')
                        @if(!$clientSecret)
                            <button wire:click="initializeStripe"
                                    wire:loading.attr="disabled"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white py-3 rounded-xl font-semibold text-sm transition">
                                <span wire:loading.remove>Load Stripe Payment</span>
                                <span wire:loading>Loading…</span>
                            </button>
                        @else
                            {{-- Stripe Elements integration --}}
                            <div id="stripe-payment-element" class="mb-4"></div>
                            <button id="stripe-submit"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition">
                                Pay ₦{{ number_format($invoice->total, 2) }} with Stripe
                            </button>
                            <script>
                                document.addEventListener('livewire:init', () => {
                                    const stripe = Stripe('{{ config('services.stripe.key') }}');
                                    const elements = stripe.elements({ clientSecret: '{{ $clientSecret }}' });
                                    const paymentEl = elements.create('payment');
                                    paymentEl.mount('#stripe-payment-element');

                                    document.getElementById('stripe-submit').addEventListener('click', async () => {
                                        const { error } = await stripe.confirmPayment({
                                            elements,
                                            confirmParams: { return_url: '{{ route('customer.dashboard') }}' }
                                        });
                                        if (error) alert(error.message);
                                    });
                                });
                            </script>
                            <script src="https://js.stripe.com/v3/"></script>
                        @endif
                    @endif
                @endif

                <div class="mt-4 text-center">
                    <a href="{{ route('customer.invoices') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Invoices</a>
                </div>
            </div>
        </div>
    </div>
</div>
