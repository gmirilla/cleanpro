<?php

namespace App\Livewire\Admin;

use App\Models\Invoice;
use App\Services\PaymentService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Invoices')]
class InvoiceViewer extends Component
{
    use WithPagination;

    public string $search='', $statusFilter='';
    public ?int $viewingId=null;

    public function updatedSearch(): void { $this->resetPage(); }

    public function markPaid(int $id, PaymentService $paymentService): void
    {
        $invoice=Invoice::with('booking')->findOrFail($id);
        $paymentService->recordCashPayment($invoice);
        $this->dispatch('notify',type:'success',message:'Invoice marked as paid.');
    }

    public function cancel(int $id): void
    {
        Invoice::findOrFail($id)->update(['status'=>'cancelled']);
        $this->dispatch('notify',type:'success',message:'Invoice cancelled.');
    }

    public function render()
    {
        $invoices=Invoice::query()
            ->when($this->search,fn($q)=>$q->where('invoice_number','like',"%{$this->search}%")
                ->orWhereHas('booking.customer.user',fn($u)=>$u->where('name','like',"%{$this->search}%")))
            ->when($this->statusFilter,fn($q)=>$q->where('status',$this->statusFilter))
            ->with(['booking.customer.user','booking.items.service'])
            ->latest()->paginate(15);

        $viewingInvoice=$this->viewingId
            ? Invoice::with(['booking.customer.user','booking.items.service','payment'])->find($this->viewingId)
            : null;

        return view('livewire.admin.invoice-viewer',compact('invoices','viewingInvoice'));
    }
}
