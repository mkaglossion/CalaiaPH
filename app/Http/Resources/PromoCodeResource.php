<?php

namespace App\Http\Resources;

use App\Helpers\PromoCodeHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoCodeResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'usage_limit' => $this->usage_limit,
            'min_booking_amount' => $this->min_booking_amount,
            'is_active' => $this->is_active,
            'remaining_uses' => $this->remaining_uses,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'formatted_discount' => PromoCodeHelper::formatDiscount($this->discount_type, $this->discount_value),
            'formatted_validity' => PromoCodeHelper::formatDateRange($this->start_date, $this->end_date),
            'status_badge' => PromoCodeHelper::getStatusBadge($this->is_active),
        ];
    }
}
