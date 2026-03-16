<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500 mt-0.5">Set per-item prices for each garment type used during laundry bookings.</p>
        </div>
        <button wire:click="openCreate"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <span>＋</span> New Garment Type
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($garments->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-5xl mb-3">👔</p>
                <p class="font-medium text-gray-500">No garment types yet.</p>
                <p class="text-sm mt-1">Click "New Garment Type" to add your first one.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-xs uppercase text-gray-400 font-semibold tracking-wide">
                        <th class="px-5 py-3 text-left">Garment Type</th>
                        <th class="px-5 py-3 text-left">Slug</th>
                        <th class="px-5 py-3 text-right">Price per Item</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($garments as $garment)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5 font-medium text-gray-800">
                                {{ $garment->label }}
                            </td>
                            <td class="px-5 py-3.5">
                                <code class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ $garment->garment_type }}</code>
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-gray-900">
                                ₦{{ number_format($garment->price, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $garment->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $garment->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEdit({{ $garment->id }})"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 px-3 py-1 rounded-lg transition">
                                        Edit
                                    </button>
                                    <button wire:click="toggleActive({{ $garment->id }})"
                                            class="text-xs text-yellow-600 hover:text-yellow-800 border border-yellow-200 hover:border-yellow-400 px-3 py-1 rounded-lg transition">
                                        {{ $garment->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button wire:click="delete({{ $garment->id }})"
                                            wire:confirm="Are you sure you want to delete '{{ $garment->label }}'? This cannot be undone."
                                            class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1 rounded-lg transition">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Info note --}}
    <p class="text-xs text-gray-400 mt-3">
        💡 Inactive garment types will not appear in the customer booking form.
        Slugs are auto-generated from the label and cannot be changed after creation.
    </p>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
         x-data x-on:keydown.escape.window="$wire.closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">

            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-800">
                    {{ $editingId ? 'Edit Garment Type' : 'New Garment Type' }}
                </h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
            </div>

            <div class="space-y-4">
                {{-- Label --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Display Name <span class="text-red-400">*</span>
                    </label>
                    <input wire:model.live="label"
                           type="text"
                           placeholder="e.g. Shirt, Trouser, Bedsheet"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    @error('label')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Slug --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Slug (auto-generated)
                        @if($editingId)
                            <span class="text-xs text-gray-400 font-normal ml-1">— cannot be changed</span>
                        @endif
                    </label>
                    <input wire:model="garment_type"
                           type="text"
                           placeholder="e.g. shirt"
                           {{ $editingId ? 'disabled' : '' }}
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono bg-gray-50
                                  focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                  {{ $editingId ? 'opacity-60 cursor-not-allowed' : '' }}">
                    @error('garment_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Lowercase letters, numbers, and underscores only.</p>
                </div>

                {{-- Price --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Price per Item (₦) <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">₦</span>
                        <input wire:model="price"
                               type="number"
                               min="0"
                               step="0.01"
                               placeholder="0.00"
                               class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    @error('price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Active toggle --}}
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Active</p>
                        <p class="text-xs text-gray-400">Inactive types won't appear in the booking form</p>
                    </div>
                    <button wire:click="$toggle('is_active')" type="button"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none
                                   {{ $is_active ? 'bg-indigo-600' : 'bg-gray-200' }}">
                        <span class="inline-block h-4 w-4 rounded-full bg-white shadow transform transition-transform
                                     {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 mt-6">
                <button wire:click="save"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition">
                    {{ $editingId ? 'Save Changes' : 'Create Garment Type' }}
                </button>
                <button wire:click="closeModal"
                        class="flex-1 border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
