<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'admin' => [
                'id' => $this->admin?->id,
                'name' => $this->admin?->name,
                'email' => $this->admin?->email,
            ],
            'vendor' => [
                'id' => $this->vendor?->id,
                'shop_name' => $this->vendor?->shop_name,
                'contact_person' => $this->vendor?->contact_person,
                'mobile' => $this->vendor?->mobile,
                'email' => $this->vendor?->email,
                'full_address' => $this->vendor?->full_address,
                'due_balance' => number_format($this->vendor?->due_balance ?? 0, 2, '.', ''),
            ],
            'placed_by' => [
                'id' => $this->placeBy?->id,
                'name' => $this->placeBy?->name,
                'email' => $this->placeBy?->email,
            ],
            'order_status' => [
                'id' => $this->orderStatus?->id,
                'name' => $this->orderStatus?->name,
                'color' => $this->orderStatus?->color ?? '#000000',
            ],
            'total_amount' => number_format($this->total_amount, 2, '.', ''),
            'paid_amount' => number_format($this->paid_amount ?? 0, 2, '.', ''),
            'due_amount' => number_format($this->order_due, 2, '.', ''),
            'total_quantity' => $this->total_quantity,
            'total_discount_amount' => number_format($this->total_discount_amount, 2, '.', ''),
            'total_return_quantity' => $this->total_return_quantity ?? 0,
            'total_damage_quantity' => $this->total_damage_quantity ?? 0,
            'total_lost_quantity' => $this->total_lost_quantity ?? 0,
            'payment_status' => $this->payment_status,
            'payment_status_text' => $this->payment_status == 1 ? 'Paid' : 'Unpaid',
            // 'total_profit' => number_format($this->total_profit, 2, '.', ''),
            'can_be_cancelled' => $this->canBeCancelled(),
            'items' => $this->whenLoaded('orderItems', function () {
                return $this->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product?->id,
                            'name' => $item->product?->name,
                            'image' => $item->product?->getFirstMediaUrl('product_image') ?: null,
                        ],
                        'quantity' => $item->quantity,
                        // 'purchase_price' => number_format($item->purchase_price, 2, '.', ''),
                        'sell_price' => number_format($item->sell_price, 2, '.', ''),
                        'total_price' => number_format($item->total_price, 2, '.', ''),
                        'discount_price' => number_format($item->discount_price ?? 0, 2, '.', ''),
                        // 'profit' => number_format(($item->sell_price - $item->purchase_price) * $item->quantity - ($item->discount_price ?? 0), 2, '.', ''),
                    ];
                });
            }, []),
            'paid_at' => $this->paid_at instanceof \Carbon\Carbon ? $this->paid_at->format('Y-m-d H:i:s') : $this->paid_at,
            'created_at' => $this->created_at instanceof \Carbon\Carbon ? $this->created_at->format('Y-m-d H:i:s') : $this->created_at,
            'updated_at' => $this->updated_at instanceof \Carbon\Carbon ? $this->updated_at->format('Y-m-d H:i:s') : $this->updated_at,
        ];
    }
}
