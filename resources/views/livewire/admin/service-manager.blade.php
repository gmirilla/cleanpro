<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search services…"
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <select wire:model.live="categoryFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Categories</option>
                <option value="cleaning">Cleaning</option>
                <option value="laundry">Laundry</option>
            </select>
            <select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button wire:click="openCreate"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            + Add Service
        </button>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($services as $service)
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:border-indigo-200 transition">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full
                            {{ $service->category === 'cleaning' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $service->category }}
                        </span>
                        <h3 class="mt-2 font-semibold text-gray-800">{{ $service->name }}</h3>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $service->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $service->status }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $service->description ?: 'No description.' }}</p>
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span class="font-bold text-gray-900">₦{{ number_format($service->base_price, 2) }}</span>
                    <span class="text-gray-400">⏱ {{ $service->duration_for_humans }}</span>
                </div>
                <div class="flex gap-2 mt-4">
                    <button wire:click="openEdit({{ $service->id }})"
                            class="flex-1 text-center text-xs border border-gray-300 hover:border-indigo-400 text-gray-600 hover:text-indigo-600 py-1.5 rounded-lg transition">
                        Edit
                    </button>
                    <button wire:click="toggleStatus({{ $service->id }})"
                            class="flex-1 text-center text-xs border border-gray-300 hover:border-yellow-400 text-gray-600 hover:text-yellow-600 py-1.5 rounded-lg transition">
                        {{ $service->status === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button wire:click="delete({{ $service->id }})"
                            wire:confirm="Delete this service?"
                            class="flex-1 text-center text-xs border border-red-200 hover:border-red-400 text-red-400 hover:text-red-600 py-1.5 rounded-lg transition">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 text-gray-400">
                <p class="text-4xl mb-2">🧹</p>
                <p>No services found. Create your first service.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $services->links() }}</div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5">{{ $editingId ? 'Edit Service' : 'New Service' }}</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input wire:model="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select wire:model="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="cleaning">Cleaning</option>
                            <option value="laundry">Laundry</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (₦) *</label>
                        <input wire:model="base_price" type="number" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @error('base_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes) *</label>
                        <input wire:model="duration_minutes" type="number" min="15"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @error('duration_minutes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="save"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition">
                    {{ $editingId ? 'Update' : 'Create' }} Service
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
