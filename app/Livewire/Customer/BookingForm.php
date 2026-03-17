<?php

namespace App\Livewire\Customer;

use App\Models\LaundryItem;
use App\Models\Service;
use App\Services\BookingService;
use App\Services\LaundryService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer')]
#[Title('New Booking')]
class BookingForm extends Component
{
    public int   $step = 1;
    public array $selectedServices = [];
    public ?int  $address_id = null;
    //public string $service_date = '', $pickup_date = '', $notes = '';
    public string $pickup_date = '', $notes = '';
    public string $detergent_type = 'standard', $special_instructions = '';
    public bool   $express_service = false;
    public $service_date;



    /**
     * Each item: ['garment_type' => string, 'quantity' => int, 'unit_price' => float]
     * unit_price is pre-populated from LaundryItem::$defaultPrices but editable by the customer.
     */

    //TO DO rewrite LaundryItem to use active items. This is no longer needed
 public array $laundryItems = [['garment_type' => 'shirt', 'quantity' => 1, 'unit_price' => 0.00],];


    public float $totalAmount      = 0;
    public float $laundryItemTotal = 0; // garment-level subtotal (shown separately)

    protected function rules(): array
    {
        return match ($this->step) {
            1 => ['selectedServices' => 'required|array|min:1'],
            2 => [
                'address_id'   => 'required|exists:addresses,id',
                'service_date' => 'required|date|after:now',
                'notes'        => 'nullable|string|max:500',
            ],
3 => ['detergent_type'=>'required','laundryItems'=>'array','laundryItems.*.garment_type'=>'required','laundryItems.*.quantity'=>'required|numeric|min:1'], 
            default => [],
        };
    }

    public function mount(): void
    {
        $customer = auth()->user()->customer;
        $this->service_date = now()->format('Y-m-d\TH:i');
        if (!$customer) abort(403, 'Customer profile not found.');

        $default = $customer->defaultAddress();
        if ($default) $this->address_id = $default->id;
    }

    // ── Service selection ────────────────────────────────────────

    public function toggleService(int $serviceId): void
    {
        if (isset($this->selectedServices[$serviceId])) {
            unset($this->selectedServices[$serviceId]);
        } else {
            $this->selectedServices[$serviceId] = 1;
        }
        $this->recalculateTotal();
    }

    public function setQuantity(int $serviceId, int $qty): void
    {
        if ($qty < 1) {
            unset($this->selectedServices[$serviceId]);
        } else {
            $this->selectedServices[$serviceId] = $qty;
        }
        $this->recalculateTotal();
    }

    // ── Laundry item management ──────────────────────────────────

    public function addLaundryItem(): void
    {
        $this->laundryItems[] = [
            'garment_type' => 'shirt',
            'quantity'     => 1,
            'unit_price'   => LaundryItem::defaultPriceFor('shirt'),
        ];
        $this->recalculateTotal();
    }

    public function removeLaundryItem(int $i): void
    {
        array_splice($this->laundryItems, $i, 1);
        $this->recalculateTotal();
    }

    /**
     * When the garment type changes, auto-update the unit_price
     * to the default for that type (customer can still override it).
     */
    public function updatedLaundryItems(mixed $value, string $key): void
    {
        // key format: "0.garment_type", "1.quantity", etc.
        [$index, $field] = explode('.', $key, 2);

        if ($field === 'garment_type') {
            $this->laundryItems[(int) $index]['unit_price'] =
                LaundryItem::defaultPriceFor($value);
        }

        $this->recalculateTotal();
    }

    // ── Navigation ───────────────────────────────────────────────
public function nextStep(): void
{
    // Cast laundry item quantities to int so validation passes
    // (Livewire sends all wire:model values as strings) fixed
    if ($this->step === 3) {
        $this->laundryItems = array_map(function ($item) {
            $item['quantity'] = (int) ($item['quantity'] ?? 1);
            return $item;
        }, $this->laundryItems);
    }

    $this->validate();
    if ($this->step === 2 && !$this->needsLaundryStep()) $this->step = 4;
    else $this->step++;
}
    public function prevStep(): void
    {
        if ($this->step === 4 && !$this->needsLaundryStep()) {
            $this->step = 2;
        } else {
            $this->step = max(1, $this->step - 1);
        }
    }

    // ── Submit ───────────────────────────────────────────────────

    public function submit(BookingService $bookingService, LaundryService $laundryService): void
    {
        $customer = auth()->user()->customer;
        $services = Service::whereIn('id', array_keys($this->selectedServices))
            ->get()->keyBy('id');

        $booking = DB::transaction(function () use ($bookingService, $laundryService, $customer, $services) {
            $items = [];
            foreach ($this->selectedServices as $id => $qty) {
                $svc     = $services[$id];
                $items[] = ['service_id' => $id, 'price' => $svc->base_price, 'quantity' => $qty];
            }

            $booking = $bookingService->create([
                'customer_id'  => $customer->id,
                'address_id'   => $this->address_id,
                'service_date' => $this->service_date,
                'pickup_date'  => $this->pickup_date ?: null,
                'notes'        => $this->notes,
                'items'        => $items,
            ]);

            if ($this->needsLaundryStep()) {
                // Pass unit_price through so LaundryService can persist it per item
                $laundryService->createOrder($booking, [
                    'detergent_type'       => $this->detergent_type,
                    'special_instructions' => $this->special_instructions,
                    'express_service'      => $this->express_service,
                    'items'                => $this->laundryItems, // includes unit_price
                ]);
            }

            return $booking;
        });

        session()->flash('success', 'Booking created! Reference: ' . $booking->booking_reference);
        $this->redirect(route('customer.bookings.show', $booking->id));
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function needsLaundryStep(): bool
    {
        return !empty($this->selectedServices) &&
            Service::whereIn('id', array_keys($this->selectedServices))
                ->where('category', 'laundry')
                ->exists();
    }

    private function recalculateTotal(): void
    {
        // Base service total
        $serviceTotal = 0;
        if (!empty($this->selectedServices)) {
            $services = Service::whereIn('id', array_keys($this->selectedServices))
                ->get()->keyBy('id');
            foreach ($this->selectedServices as $id => $qty) {
                if (isset($services[$id])) {
                    $serviceTotal += $services[$id]->base_price * $qty;
                }
            }
        }

        // Garment-level laundry total
        $this->laundryItemTotal = 0;
        foreach ($this->laundryItems as $item) {
            $price    = (float) ($item['unit_price'] ?? LaundryItem::defaultPriceFor($item['garment_type'] ?? 'others'));
            $quantity = (int) ($item['quantity'] ?? 1);
            $this->laundryItemTotal += $price * $quantity;
        }

        $this->totalAmount = $serviceTotal + $this->laundryItemTotal;
    }

    // ── Render ───────────────────────────────────────────────────

    public function render()
    {
        $customer = auth()->user()->customer;
        $services = Service::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $addresses = $customer->addresses()->get();

        $selectedServiceModels = !empty($this->selectedServices)
            ? Service::whereIn('id', array_keys($this->selectedServices))->get()->keyBy('id')
            : collect();

        $garmentPrices = LaundryItem::$defaultPrices;

        return view('livewire.customer.booking-form', compact(
            'services',
            'addresses',
            'selectedServiceModels',
            'garmentPrices',
        ));
    }
}
