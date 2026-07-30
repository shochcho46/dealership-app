<?php

namespace Modules\Admin\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesPerformanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource['user'];
        $userRole = $user->roles->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $userRole ? $userRole->name : null,
            'image' => $user->getFirstMediaUrl('profile_picture') ?: null,
            'sales_target' => $user->sales_target ? number_format($user->sales_target, 2, '.', '') : null,
            'sales' => [
                'amount' => number_format($this->resource['sales']['amount'], 2, '.', ''),
                'order_count' => $this->resource['sales']['order_count'],
                'collections_received' => number_format($this->resource['sales']['collections_received'], 2, '.', ''),
                'due_amount' => number_format($this->resource['sales']['due_amount'], 2, '.', ''),
                'collection_percentage' => number_format($this->resource['sales']['collection_percentage'], 2, '.', ''),
            ],
            'individual_collections' => [
                'from_current_period_orders' => number_format($this->resource['individual_collections']['from_current_period_orders'], 2, '.', ''),
                'from_previous_period_orders' => number_format($this->resource['individual_collections']['from_previous_period_orders'], 2, '.', ''),
            ],
            'dsr_collections' => number_format($this->resource['dsr_collections'], 2, '.', ''),
            'target_metrics' => $this->resource['target_metrics'] ? [
                'completion_percentage' => number_format($this->resource['target_metrics']['completion_percentage'], 2, '.', ''),
                'amount_remaining' => number_format($this->resource['target_metrics']['amount_remaining'], 2, '.', ''),
                'status' => $this->resource['target_metrics']['status'],
            ] : null,
        ];
    }
}
