<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetSearchedProductsRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:1',
            'type' => 'nullable|string|in:all,veg,non_veg',
            'new' => 'nullable|boolean',
            'popular' => 'nullable|boolean',
            'rating' => 'nullable|boolean',
            'rating_3_plus' => 'nullable|boolean',
            'rating_4_plus' => 'nullable|boolean',
            'rating_5' => 'nullable|boolean',
            'discounted' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:asc,desc,low,high',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => translate('messages.name_max_255'),
            'category_id.exists' => translate('messages.category_not_found'),
            'restaurant_id.exists' => translate('messages.restaurant_not_found'),
            'limit.min' => translate('messages.limit_min_1'),
            'limit.max' => translate('messages.limit_max_100'),
            'offset.min' => translate('messages.offset_min_1'),
            'type.in' => translate('messages.invalid_type'),
            'sort_by.in' => translate('messages.invalid_sort_option'),
        ];
    }
}
