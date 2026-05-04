<?php

namespace App\Http\Requests\Admin;

class UpdatePromoCodeRequest extends StorePromoCodeRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $promoCodeId = $this->route('promo_code') ?? $this->route('id');

        return [
            'code' => 'required|string|max:50|unique:promo_codes,code,' . $promoCodeId,
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'min_booking_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ];
    }
}