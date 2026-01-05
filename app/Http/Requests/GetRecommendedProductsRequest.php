<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetRecommendedProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'name' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:1',
            'type' => 'nullable|string|in:all,veg,non_veg',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'restaurant_id.required' => translate('messages.restaurant_id_required'),
            'restaurant_id.exists' => translate('messages.restaurant_not_found'),
            'name.max' => translate('messages.name_max_255'),
            'limit.min' => translate('messages.limit_min_1'),
            'limit.max' => translate('messages.limit_max_100'),
            'offset.min' => translate('messages.offset_min_1'),
            'type.in' => translate('messages.invalid_type'),
        ];
    }
}
