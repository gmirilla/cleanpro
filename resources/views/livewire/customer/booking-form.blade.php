<div>
    {{-- Progress bar --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            @php $steps = ['Choose Services', 'Booking Details', 'Laundry Details', 'Confirm']; @endphp
            @foreach($steps as $i => $label)
                <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                        {{ $step > ($i+1) ? 'bg-green-500 text-white' : ($step === ($i+1) ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                        {{ $step > ($i + 1) ? '✓' : ($i + 1) }}
                    </div>
                    <span class="ml-2 text-xs hidden md:inline {{ $step === ($i+1) ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">
                        {{ $label }}
                    </span>
                    @if($i < count($steps) - 1)
                        <div class="flex-1 h-0.5 mx-3 {{ $step > ($i+1) ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ════════════════ STEP 1: Choose Services ════════════════ --}}
    @if($step === 1)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Select Services</h2>
            <p class="text-sm text-gray-500">Choose one or more services for your booking.</p>
        </div>
        @error('selectedServices') <p class="text-red-500 text-sm mb-4">{{ $message }}</p> @enderror

        @foreach($services as $category => $categoryServices)
            <div class="mb-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">
                    {{ $category === 'cleaning' ? '🧹 Cleaning Services' : '👕 Laundry Services' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($categoryServices as $service)
                        @php $selected = isset($selectedServices[$service->id]); @endphp
                        <div wire:click="toggleService({{ $service->id }})"
                             class="cursor-pointer border-2 rounded-xl p-4 transition
                                {{ $selected ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300 bg-white' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">{{ $service->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $service->description }}</p>
                                </div>
                                <span class="text-sm font-bold text-indigo-600 ml-3 whitespace-nowrap">
                                    ₦{{ number_format($service->base_price, 2) }}
                                </span>
                            </div>
                            @if($selected)
                                <div class="flex items-center gap-2 mt-3" wire:click.stop>
                                    <button wire:click="setQuantity({{ $service->id }}, {{ ($selectedServices[$service->id] ?? 1) - 1 }})"
                                            class="w-7 h-7 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 text-sm font-bold">−</button>
                                    <span class="font-semibold text-gray-800 w-4 text-center">{{ $selectedServices[$service->id] ?? 1 }}</span>
                                    <button wire:click="setQuantity({{ $service->id }}, {{ ($selectedServices[$service->id] ?? 1) + 1 }})"
                                            class="w-7 h-7 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 text-sm font-bold">+</button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($totalAmount > 0)
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-right">
                <span class="text-sm text-indigo-600">Services subtotal: </span>
                <span class="font-bold text-indigo-700">₦{{ number_format($totalAmount, 2) }}</span>
            </div>
        @endif


    {{-- ════════════════ STEP 2: Booking Details ════════════════ --}}
    @elseif($step === 2)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Booking Details</h2>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Service Address *</label>
                <select wire:model="address_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <option value="">Select address…</option>
                    @foreach($addresses as $addr)
                        <option value="{{ $addr->id }}">{{ $addr->label }} — {{ $addr->address }}, {{ $addr->city }}</option>
                    @endforeach
                </select>
                @error('address_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Date & Time *</label>
                    <input wire:model="service_date" type="datetime-local"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    @error('service_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Date (Laundry)</label>
                    <input wire:model="pickup_date" type="datetime-local"
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


    {{-- ════════════════ STEP 3: Laundry Details ════════════════ --}}
    @elseif($step === 3)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Laundry Details</h2>
            <p class="text-sm text-gray-500">Add your garments — pricing is calculated per item.</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">

            {{-- Detergent & Express --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Detergent Type</label>
                    <select wire:model="detergent_type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="standard">Standard</option>
                        <option value="hypoallergenic">Hypoallergenic</option>
                        <option value="eco">Eco-Friendly</option>
                        <option value="customer_supplied">I'll Supply My Own</option>
                    </select>
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="express_service" type="checkbox"
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="text-sm text-gray-700">⚡ Express Service</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                <textarea wire:model="special_instructions" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none"></textarea>
            </div>

            {{-- ── Garment Items Table ── --}}
            <div>
                <div class="flex justify-between items-center mb-3">
                    <label class="text-sm font-semibold text-gray-700">Garment List</label>
                    <button wire:click="addLaundryItem" type="button"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium border border-indigo-300 hover:border-indigo-500 px-3 py-1 rounded-lg transition">
                        + Add Item
                    </button>
                </div>

                @error('laundryItems') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

                {{-- Table header --}}
                <div class="hidden sm:grid grid-cols-12 gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider px-1 mb-2">
                    <div class="col-span-4">Garment Type</div>
                    <div class="col-span-2 text-center">Qty</div>
                    <div class="col-span-3 text-right">Unit Price (₦)</div>
                    <div class="col-span-2 text-right">Subtotal</div>
                    <div class="col-span-1"></div>
                </div>

                <div class="space-y-2">
                    @foreach($laundryItems as $i => $item)
                        @php
                            $itemSubtotal = (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 1);
                        @endphp
                        <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 rounded-lg p-2 border border-gray-200">

                            {{-- Garment type --}}
                            <div class="col-span-12 sm:col-span-4">
                                <label class="text-xs text-gray-400 sm:hidden mb-1 block">Garment</label>
                                <select wire:model.live="laundryItems.{{ $i }}.garment_type"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm bg-white focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                    @foreach(LaundryItem::activeGarmentTypes() as $type)
                                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                                @error("laundryItems.{$i}.garment_type")
                                    <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Quantity --}}
                            <div class="col-span-4 sm:col-span-2">
                                <label class="text-xs text-gray-400 sm:hidden mb-1 block">Qty</label>
                                <input wire:model.live="laundryItems.{{ $i }}.quantity"
                                       type="number" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center bg-white focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                @error("laundryItems.{$i}.quantity")
                                    <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Unit price --}}
                            <div class="col-span-4 sm:col-span-3">
                                <label class="text-xs text-gray-400 sm:hidden mb-1 block">Unit Price</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₦</span>
                                    <input wire:model.live="laundryItems.{{ $i }}.unit_price"
                                           type="number" min="0" step="50"
                                           class="w-full border border-gray-300 rounded-lg pl-6 pr-2 py-1.5 text-sm text-right bg-white focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                </div>
                                @error("laundryItems.{$i}.unit_price")
                                    <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Subtotal (read-only) --}}
                            <div class="col-span-3 sm:col-span-2 text-right">
                                <label class="text-xs text-gray-400 sm:hidden mb-1 block">Subtotal</label>
                                <span class="text-sm font-semibold text-gray-800">
                                    ₦{{ number_format($itemSubtotal, 2) }}
                                </span>
                            </div>

                            {{-- Remove button --}}
                            <div class="col-span-1 text-center">
                                @if(count($laundryItems) > 1)
                                    <button wire:click="removeLaundryItem({{ $i }})" type="button"
                                            class="text-red-400 hover:text-red-600 text-lg leading-none font-bold">×</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Garment subtotal footer --}}
                @if($laundryItemTotal > 0)
                    <div class="flex justify-between items-center mt-3 px-2 pt-3 border-t border-gray-200 text-sm">
                        <span class="text-gray-500">Garment items total</span>
                        <span class="font-bold text-gray-800">₦{{ number_format($laundryItemTotal, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Running total banner --}}
        <div class="mt-4 bg-indigo-50 border border-indigo-200 rounded-xl p-4">
            <div class="flex justify-between text-sm text-indigo-600 mb-1">
                <span>Services subtotal</span>
                <span>₦{{ number_format($totalAmount - $laundryItemTotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-indigo-600 mb-2">
                <span>Garment items</span>
                <span>₦{{ number_format($laundryItemTotal, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-indigo-800 text-base border-t border-indigo-200 pt-2">
                <span>Estimated Total</span>
                <span>₦{{ number_format($totalAmount, 2) }}</span>
            </div>
            <p class="text-xs text-indigo-400 mt-1">Final amount may be adjusted after weighing / inspection.</p>
        </div>


    {{-- ════════════════ STEP 4: Confirm ════════════════ --}}
    @elseif($step === 4)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Confirm Booking</h2>
            <p class="text-sm text-gray-500">Please review your booking details before submitting.</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 text-sm">

            {{-- Services --}}
            <div>
                <p class="font-semibold text-gray-600 mb-2 text-xs uppercase tracking-wider">Services</p>
                @foreach($selectedServiceModels as $id => $svc)
                    <div class="flex justify-between py-1.5 border-b border-gray-50 last:border-0">
                        <span class="text-gray-700">{{ $svc->name }} × {{ $selectedServices[$id] }}</span>
                        <span class="font-medium">₦{{ number_format($svc->base_price * $selectedServices[$id], 2) }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Garment items (if laundry) --}}
            @if($laundryItemTotal > 0 && count($laundryItems) > 0)
                <div class="border-t border-gray-100 pt-4">
                    <p class="font-semibold text-gray-600 mb-2 text-xs uppercase tracking-wider">Garment Items</p>
                    @foreach($laundryItems as $item)
                        @php
                            $sub = (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 1);
                        @endphp
                        <div class="flex justify-between py-1.5 border-b border-gray-50 last:border-0">
                            <span class="text-gray-700">
                                {{ ucfirst($item['garment_type']) }} × {{ $item['quantity'] }}
                                <span class="text-gray-400 text-xs">(₦{{ number_format($item['unit_price'], 2) }} each)</span>
                            </span>
                            <span class="font-medium">₦{{ number_format($sub, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Total --}}
            <div class="border-t border-gray-200 pt-3 flex justify-between font-bold text-gray-900 text-base">
                <span>Estimated Total</span>
                <span>₦{{ number_format($totalAmount, 2) }}</span>
            </div>

            {{-- Booking info --}}
            <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Service Date</span>
                    <span class="font-medium">{{ $service_date }}</span>
                </div>
                @if($pickup_date)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pickup Date</span>
                        <span class="font-medium">{{ $pickup_date }}</span>
                    </div>
                @endif
                @if($notes)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Notes</span>
                        <span class="font-medium">{{ $notes }}</span>
                    </div>
                @endif
                @if($express_service)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Express Service</span>
                        <span class="font-medium text-orange-600">⚡ Yes</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Navigation ── --}}
    <div class="flex justify-between mt-8">
        @if($step > 1)
            <button wire:click="prevStep"
                    class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-6 py-2.5 rounded-lg text-sm transition">
                ← Back
            </button>
        @else
            <div></div>
        @endif

        @if($step < 4)
            <button wire:click="nextStep"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-lg text-sm font-medium transition">
                Continue →
            </button>
        @else
            <button wire:click="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-2.5 rounded-lg text-sm font-medium transition">
                ✓ Confirm Booking
            </button>
        @endif
    </div>
</div>
