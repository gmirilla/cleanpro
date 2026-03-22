<div wire:loading.class="pointer-events-none">

    {{-- Full-page loading overlay --}}
    <div wire:loading wire:target="nextStep,submit"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white/70 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl px-10 py-8 flex flex-col items-center gap-4 border border-gray-100">
            <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm font-semibold text-gray-700">Please wait…</p>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            @php $steps = ['Choose Services','Booking Details','Laundry Details','Confirm']; @endphp
            @foreach ($steps as $i => $label)
                <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                        {{ $step > $i + 1 ? 'bg-green-500 text-white' : ($step === $i + 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                        {{ $step > $i + 1 ? '✓' : $i + 1 }}
                    </div>
                    <span
                        class="ml-2 text-xs hidden md:inline {{ $step === $i + 1 ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                    @if ($i < count($steps) - 1)
                        <div class="flex-1 h-0.5 mx-3 {{ $step > $i + 1 ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────── --}}
    {{-- Step 1: Choose Services                                 --}}
    {{-- ─────────────────────────────────────────────────────── --}}
    @if ($step === 1)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Select Services</h2>
            <p class="text-sm text-gray-500">Choose one or more services for your booking.</p>
        </div>
        @error('selectedServices')
            <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
        @enderror

        @foreach ($services as $category => $categoryServices)
            <div class="mb-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">
                    {{ $category === 'cleaning' ? '🧹 Cleaning Services' : '👕 Laundry Services' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach ($categoryServices as $service)
                        @php $selected = isset($selectedServices[$service->id]); @endphp
                        <div wire:click="toggleService({{ $service->id }})"
                            class="cursor-pointer border-2 rounded-xl p-4 transition
                                {{ $selected ? 'border-indigo-500 bg-indigo-300' : 'border-gray-200 hover:border-indigo-300' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $service->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">⏱ {{ $service->duration_for_humans }}</p>
                                    @if ($service->description)
                                        <p class="text-xs text-black-400 mt-1 line-clamp-2">{{ $service->description }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right ml-4 flex-shrink-0">
                                    <p class="font-bold text-gray-800">₦{{ number_format($service->base_price, 2) }}
                                    </p>
                                    @if ($selected)
                                        <span class="text-indigo-600 text-xs">✓ Selected</span>
                                    @endif
                                </div>
                            </div>
                            @if ($selected)
                                <div class="flex items-center gap-2 mt-3" x-on:click.stop>
                                    <div hidden>
                                        <span class="text-xs text-gray-500">Qty:</span>
                                        <button
                                            wire:click.stop="setQuantity({{ $service->id }}, {{ ($selectedServices[$service->id] ?? 1) - 1 }})"
                                            class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 font-bold text-sm hover:bg-indigo-200">−</button>
                                        <span
                                            class="text-sm font-semibold w-4 text-center">{{ $selectedServices[$service->id] ?? 1 }}</span>
                                        <button
                                            wire:click.stop="setQuantity({{ $service->id }}, {{ ($selectedServices[$service->id] ?? 1) + 1 }})"
                                            class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 font-bold text-sm hover:bg-indigo-200">+</button>

                                    </div>

                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if ($totalAmount > 0)
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex justify-between items-center mb-6">
                <span class="text-indigo-700 font-medium">Estimated Total</span>
                <span class="text-2xl font-bold text-indigo-700">₦{{ number_format($totalAmount, 2) }}</span>
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- Step 2: Booking Details                                 --}}
        {{-- ─────────────────────────────────────────────────────── --}}
    @elseif($step === 2)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Booking Details</h2>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address *</label>
                <select wire:model="address_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <option value="">Select address…</option>
                    @foreach ($addresses as $addr)
                        <option value="{{ $addr->id }}">{{ $addr->label }}: {{ $addr->full_address }}</option>
                    @endforeach
                </select>
                @error('address_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Date & Time *</label>
                    <input wire:model="service_date" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}"
                        min="{{ now()->format('Y-m-d\TH:i') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    @error('service_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Date (Laundry)</label>
                    <input wire:model="pickup_date" type="datetime-local" min="{{ now()->format('Y-m-d\TH:i') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Special Notes</label>
                <textarea wire:model="notes" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"
                    placeholder="Any special instructions…"></textarea>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- Step 3: Laundry Details                                 --}}
        {{-- ─────────────────────────────────────────────────────── --}}
    @elseif($step === 3)
        @php
            //Load active garment types with their admin-set prices
            $garmentPrices = \App\Models\GarmentPrice::active()->orderBy('label')->get()->keyBy('garment_type');
        @endphp

        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Laundry Details</h2>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">

            {{-- Detergent & Express --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Detergent Type</label>
                    <select wire:model="detergent_type"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="standard">Standard</option>
                        <option value="hypoallergenic">Hypoallergenic</option>
                        <option value="customer_supplied">I'll Supply My Own</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="express_service" type="checkbox"
                            class="rounded border-gray-300 text-indigo-600">
                        <span class="text-sm text-gray-700">Express Service</span>
                    </label>
                </div>
            </div>

            {{-- Special Instructions --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                <textarea wire:model="special_instructions" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none"></textarea>
            </div>

            {{-- Garment List --}}
            <div>
                <div class="flex justify-between items-center mb-3">
                    <label class="text-sm font-medium text-gray-700">Garment List</label>
                    <button wire:click="addLaundryItem" type="button"
                        class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 px-3 py-1 rounded-lg transition">
                        + Add Item
                    </button>
                </div>

                @if ($garmentPrices->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-700">
                        ⚠ No garment types have been configured yet. Please contact support.
                    </div>
                @else
                    {{-- Header row --}}
                    <div
                        class="grid grid-cols-12 gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1 px-1">
                        <div class="col-span-5">Garment Type</div>
                        <div class="col-span-3 text-right">Unit Price</div>
                        <div class="col-span-2 text-center">Qty</div>
                        <div class="col-span-2 text-right">Subtotal</div>
                    </div>

                    <div class="space-y-2">
                        @foreach ($laundryItems as $i => $item)
                            @php
                                $unitPrice = (float) ($garmentPrices[$item['garment_type']]->price ?? 0);
                                $lineTotal = $unitPrice * (int) ($item['quantity'] ?? 1);
                            @endphp
                            <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 rounded-lg px-3 py-2">

                                {{-- Garment type dropdown --}}
                                <div class="col-span-5">
                                    <select wire:model.live="laundryItems.{{ $i }}.garment_type"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm bg-white focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                        @foreach ($garmentPrices as $slug => $gp)
                                            <option value="{{ $slug }}">{{ $gp->label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Unit price (read-only) --}}
                                <div class="col-span-3 text-right">
                                    <span class="text-sm text-gray-500 font-medium">
                                        ₦{{ number_format($unitPrice, 2) }}
                                    </span>
                                </div>

                                {{-- Quantity --}}
                                <div class="col-span-2 flex justify-center">
                                    <input wire:model.live="laundryItems.{{ $i }}.quantity" type="number"
                                        min="1"
                                        class="w-16 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                </div>

                                {{-- Line total + remove --}}
                                <div class="col-span-2 flex items-center justify-end gap-2">
                                    <span class="text-sm font-semibold text-gray-700">
                                        ₦{{ number_format($lineTotal, 2) }}
                                    </span>
                                    @if (count($laundryItems) > 1)
                                        <button wire:click="removeLaundryItem({{ $i }})" type="button"
                                            class="text-red-400 hover:text-red-600 text-lg leading-none flex-shrink-0">×</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Garment subtotal --}}
                    @php
                        $garmentTotal = collect($laundryItems)->reduce(function ($carry, $item) use ($garmentPrices) {
                            $price = (float) ($garmentPrices[$item['garment_type']]->price ?? 0);
                            return $carry + $price * (int) ($item['quantity'] ?? 1);
                        }, 0);
                    @endphp
                    <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                        <span class="text-sm text-gray-500">Garment Subtotal</span>
                        <span class="text-base font-bold text-gray-800">₦{{ number_format($garmentTotal, 2) }}</span>
                    </div>

                    <p class="text-xs text-gray-400 mt-2">
                        🔒 Prices are set by the admin and cannot be modified.
                    </p>
                @endif
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- Step 4: Confirm                                         --}}
        {{-- ─────────────────────────────────────────────────────── --}}
    @elseif($step === 4)
        @php
            $garmentPrices = \App\Models\GarmentPrice::active()->orderBy('label')->get()->keyBy('garment_type');
        @endphp

        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Confirm Booking</h2>
            <p class="text-sm text-gray-500">Please review your booking details before submitting.</p>
        </div>

        <div class="space-y-4">

            {{-- Services --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="font-semibold text-gray-700 mb-3">Services Selected</p>
                @foreach ($selectedServiceModels as $id => $svc)
                    <div class="flex justify-between py-1.5 border-b border-gray-50 last:border-0 text-sm">
                        <span class="text-gray-700">{{ $svc->name }} × {{ $selectedServices[$id] }}</span>
                        <span
                            class="font-medium text-gray-800">₦{{ number_format($svc->base_price * $selectedServices[$id], 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between font-bold text-gray-800 pt-3 mt-1 border-t border-gray-200 text-sm">
                    <span>Services Total</span>
                    <span>₦{{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>

            {{-- Laundry garments (if applicable) --}}
            @if (!empty($laundryItems) && $garmentPrices->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <p class="font-semibold text-gray-700 mb-3">👕 Garment Breakdown</p>
                    @php $garmentTotal = 0; @endphp
                    @foreach ($laundryItems as $item)
                        @php
                            $gp = $garmentPrices[$item['garment_type']] ?? null;
                            $unitPrice = (float) ($gp->price ?? 0);
                            $lineTotal = $unitPrice * (int) ($item['quantity'] ?? 1);
                            $garmentTotal += $lineTotal;
                        @endphp
                        @if ($unitPrice > 0)
                            <div class="flex justify-between py-1.5 border-b border-gray-50 last:border-0 text-sm">
                                <span class="text-gray-700">
                                    {{ $gp->label ?? ucfirst($item['garment_type']) }}
                                    × {{ $item['quantity'] }}
                                    <span class="text-gray-400 text-xs ml-1">(₦{{ number_format($unitPrice, 2) }}
                                        each)</span>
                                </span>
                                <span class="font-medium text-gray-800">₦{{ number_format($lineTotal, 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div
                        class="flex justify-between font-bold text-gray-800 pt-3 mt-1 border-t border-gray-200 text-sm">
                        <span>Garments Total</span>
                        <span>₦{{ number_format($garmentTotal, 2) }}</span>
                    </div>
                </div>
            @endif

            {{-- Booking info --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-sm space-y-2">
                <p class="font-semibold text-gray-700 mb-3">Booking Info</p>
                <div class="flex justify-between">
                    <span class="text-gray-500">Service Date</span>
                    <span class="font-medium text-gray-800">{{ $service_date }}</span>
                </div>
                @if ($pickup_date)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pickup Date</span>
                        <span class="font-medium text-gray-800">{{ $pickup_date }}</span>
                    </div>
                @endif
                @if ($notes)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Notes</span>
                        <span class="font-medium text-gray-800">{{ $notes }}</span>
                    </div>
                @endif
            </div>

        </div>
    @endif

    {{-- ─────────────────────────────────────────────────────── --}}
    {{-- Navigation                                              --}}
    {{-- ─────────────────────────────────────────────────────── --}}
    <div class="flex justify-between mt-8">
        @if ($step > 1)
            <button wire:click="prevStep"
                class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-6 py-2.5 rounded-lg text-sm transition">
                ← Back
            </button>
        @else
            <div></div>
        @endif

        @if ($step < 4)
            <button wire:click="nextStep" wire:loading.attr="disabled" wire:target="nextStep"
                class="relative bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white px-8 py-2.5 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <svg wire:loading wire:target="nextStep" class="animate-spin h-4 w-4 text-white"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                    </path>
                </svg>
                <span wire:loading.remove wire:target="nextStep">Continue →</span>
                <span wire:loading wire:target="nextStep">Processing…</span>
            </button>
        @else
            <button wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
                class="relative bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed text-white px-8 py-2.5 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <svg wire:loading wire:target="submit" class="animate-spin h-4 w-4 text-white"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                    </path>
                </svg>
                <span wire:loading.remove wire:target="submit">✓ Confirm Booking</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </button>
        @endif
    </div>
</div>
