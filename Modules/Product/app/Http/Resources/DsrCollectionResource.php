<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DsrCollectionResource extends JsonResource
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
            'vendor' => [
                'id' => $this->vendor?->id,
                'shop_name' => $this->vendor?->shop_name,
                'contact_person' => $this->vendor?->contact_person,
                'mobile' => $this->vendor?->mobile,
                'email' => $this->vendor?->email,
                'full_address' => $this->vendor?->full_address,
                'due_balance' => number_format($this->vendor?->due_balance ?? 0, 2, '.', ''),
            ],
            'payment_method' => [
                'id' => $this->paymentMethod?->id,
                'name' => $this->paymentMethod?->name,
                'account_name' => $this->paymentMethod?->account_name,
            ],
            'amount' => number_format($this->amount, 2, '.', ''),
            'collection_date' => $this->collection_date instanceof \Carbon\Carbon 
                ? $this->collection_date->format('Y-m-d') 
                : $this->collection_date,
            'note' => $this->note,
            'created_by' => [
                'id' => $this->createdBy?->id,
                'name' => $this->createdBy?->name,
                'email' => $this->createdBy?->email,
            ],
            'deposite_by' => [
                'id' => $this->depositeBy?->id,
                'name' => $this->depositeBy?->name,
                'email' => $this->depositeBy?->email,
            ],
            'created_at' => $this->created_at instanceof \Carbon\Carbon 
                ? $this->created_at->format('Y-m-d H:i:s') 
                : $this->created_at,
            'updated_at' => $this->updated_at instanceof \Carbon\Carbon 
                ? $this->updated_at->format('Y-m-d H:i:s') 
                : $this->updated_at,
        ];
    }
}
