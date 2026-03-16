<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\LaundryItem;
use App\Models\LaundryOrder;

class LaundryService
{
    /**
     * Create a laundry order with priced garment items, then
     * recalculate the parent booking's total_amount to include
     * the garment-level costs on top of the base service price.
     */
    public function createOrder(Booking $booking, array $data): LaundryOrder
    {
        $order = LaundryOrder::create([
            'booking_id'           => $booking->id,
            'weight'               => $data['weight'] ?? null,
            'garment_count'        => $data['garment_count'] ?? null,
            'detergent_type'       => $data['detergent_type'] ?? 'standard',
            'special_instructions' => $data['special_instructions'] ?? null,
            'express_service'      => $data['express_service'] ?? false,
        ]);

        foreach ($data['items'] ?? [] as $item) {
            $garmentType = $item['garment_type'];
            $quantity    = (int) ($item['quantity'] ?? 1);

            // Use the provided unit_price if supplied, otherwise fall back to the
            // model's default price map so pricing is always set.
            $unitPrice = isset($item['unit_price']) && (float) $item['unit_price'] > 0
                ? (float) $item['unit_price']
                : LaundryItem::defaultPriceFor($garmentType);

            LaundryItem::create([
                'laundry_order_id' => $order->id,
                'garment_type'     => $garmentType,
                'quantity'         => $quantity,
                'unit_price'       => $unitPrice,
                // subtotal is auto-computed in LaundryItem::boot()
                'service_type'     => $item['service_type'] ?? null,
                'status'           => 'received',
            ]);
        }

        $order->load('items');

        // ── Recalculate booking total to include garment costs ──────
        // The booking already has a total based on the top-level service
        // base_price. We now ADD the sum of all garment subtotals so that
        // the invoice reflects what the customer actually brought in.
        $garmentTotal = $order->items->sum('subtotal');

        if ($garmentTotal > 0) {
            $booking->increment('total_amount', $garmentTotal);
        }

        return $order;
    }

    /**
     * Recalculate the laundry subtotal for an existing order and
     * sync it back to the booking total.
     */
    public function recalculateOrderTotal(LaundryOrder $order): void
    {
        $order->load('items');
        $garmentTotal = $order->items->sum('subtotal');

        // Recompute from scratch: base service amount + laundry garment total
        $booking = $order->booking()->with('items')->first();
        $serviceTotal = $booking->items->sum('subtotal');

        $booking->update(['total_amount' => $serviceTotal + $garmentTotal]);
    }

    public function updateItemStatus(LaundryItem $item, string $status): void
    {
        $item->update(['status' => $status]);
    }

    public function advanceAllItems(LaundryOrder $order): void
    {
        $pipeline = LaundryItem::$statuses;

        foreach ($order->items as $item) {
            $currentIndex = array_search($item->status, $pipeline);
            if ($currentIndex !== false && isset($pipeline[$currentIndex + 1])) {
                $item->update(['status' => $pipeline[$currentIndex + 1]]);
            }
        }
    }

    public function allItemsReady(LaundryOrder $order): bool
    {
        return $order->items->every(fn($i) => in_array($i->status, ['ready', 'delivered']));
    }
}
