<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'color' => [
                'id' => $this->color?->id,
                'name' => $this->color?->name,
            ],
            'company' => [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
            ],
            'unit' => [
                'id' => $this->unit?->id,
                'name' => $this->unit?->name,
            ],
            'measurement_unit_name' => $this->measurement_unit_name,
            'measurement_unit_number' => $this->measurement_unit_number,
            'package_unit_name' => $this->package_unit_name,
            'package_unit_quantity' => $this->package_unit_quantity,
            'discount_type' => $this->discount_type,
            'discount_amount' => $this->discount_amount,
            'status' => $this->status,
            'status_text' => $this->status ? 'Active' : 'Inactive',
            'image' => [
                'url' => $this->getFirstMediaUrl('product_image') ?: null,
                'thumb_url' => $this->getFirstMediaUrl('product_image', 'thumb') ?: null,
            ],
            'quantity_available' => $this->total_available_quantity,
            'sell_price' => number_format($this->highest_sell_price, 2, '.', ''),
            'stocks' => $this->stocks->map(function ($stock) {
                $availableQty = $stock->quantity - $stock->sold_quantity - $stock->damage_quantity 
                              - $stock->stolen_quantity - $stock->transfer_quantity - $stock->froze_quantity;
                return [
                    'stock_id' => $stock->id,
                    'warehouse_id' => $stock->warehouse_id,
                    'batch_id' => $stock->batch_id,
                    'purchase_price' => number_format($stock->purchase_price, 2, '.', ''),
                    'sell_price' => number_format($stock->sell_price, 2, '.', ''),
                    'available_quantity' => $availableQty,
                    'manufacture_date' => $stock->manufacture_date?->format('Y-m-d'),
                    'expire_date' => $stock->expire_date?->format('Y-m-d'),
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
