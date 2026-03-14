<?php

namespace App\Livewire\Admin;

use App\Models\LaundryItem;
use App\Models\LaundryOrder;
use App\Services\LaundryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Laundry Orders')]
class LaundryOrderManager extends Component
{
    use WithPagination;

    public string $search='';
    public ?int $viewingId=null;

    public function updateItemStatus(int $itemId, string $status, LaundryService $laundryService): void
    {
        $laundryService->updateItemStatus(LaundryItem::findOrFail($itemId), $status);
        $this->dispatch('notify',type:'success',message:'Status updated.');
    }

    public function advanceAll(int $orderId, LaundryService $laundryService): void
    {
        $laundryService->advanceAllItems(LaundryOrder::findOrFail($orderId));
        $this->dispatch('notify',type:'success',message:'All items advanced.');
    }

    public function render()
    {
        $orders = LaundryOrder::query()
            ->when($this->search, fn($q) =>
                $q->whereHas('booking', fn($b) =>
                    $b->where('booking_reference','like',"%{$this->search}%")))
            ->with(['booking.customer.user','items'])
            ->latest()->paginate(15);

        return view('livewire.admin.laundry-order-manager', compact('orders'));
    }
}
