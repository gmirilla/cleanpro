<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\LaundryItem;
use App\Models\LaundryOrder;

class LaundryService
{
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
            LaundryItem::create([
                'laundry_order_id' => $order->id,
                'garment_type'     => $item['garment_type'],
                'quantity'         => $item['quantity'],
                'service_type'     => $item['service_type'] ?? null,
                'status'           => 'received',
            ]);
        }

        return $order->load('items');
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
