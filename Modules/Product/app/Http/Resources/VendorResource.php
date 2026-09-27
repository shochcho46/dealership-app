<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
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
            'uuid' => $this->uuid,
            'shop_name' => $this->shop_name,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'country' => [
                'id' => $this->country?->id,
                'name' => $this->country?->name,
                'iso' => $this->country?->iso,
            ],
            'address' => [
                'full_address' => $this->full_address,
                'lat' => $this->lat,
                'long' => $this->long,
                'location_status' => ($this->lat && $this->long) ? 'yes' : 'no',
                'location_status_color' => ($this->lat && $this->long) ? '#800080' : '#ff0000',
            ],
            'status' => $this->status,
            'status_text' => $this->status ? 'Active' : 'Inactive',
            'image' => [
                'url' => $this->getFirstMediaUrl('vendor_image') ?: null,
                'thumb_url' => $this->getFirstMediaUrl('vendor_image', 'thumb') ?: null,
            ],
            'due_balance' => number_format($this->due_balance, 2, '.', ''),
            'old_due' => number_format($this->old_due, 2, '.', ''),
            'total_credit' => number_format($this->vendorAccounts()->where('type', 2)->sum('amount'), 2, '.', ''),
            'total_debit' => number_format($this->vendorAccounts()->where('type', 1)->sum('amount'), 2, '.', ''),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
