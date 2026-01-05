<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitProductReviewRequest extends FormRequest
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
            'food_id' => 'required|integer|exists:food,id',
            'order_id' => 'required|integer|exists:orders,id',
            'comment' => 'required|string|max:1000',
            'rating' => 'required|numeric|min:1|max:5',
            'attachment.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'food_id.required' => translate('messages.food_id_required'),
            'food_id.exists' => translate('messages.food_not_found'),
            'order_id.required' => translate('messages.order_id_required'),
            'order_id.exists' => translate('messages.order_not_found'),
            'comment.required' => translate('messages.comment_required'),
            'comment.max' => translate('messages.comment_max_1000'),
            'rating.required' => translate('messages.rating_required'),
            'rating.min' => translate('messages.rating_min_1'),
            'rating.max' => translate('messages.rating_max_5'),
            'attachment.*.mimes' => translate('messages.invalid_file_type'),
            'attachment.*.max' => translate('messages.file_size_max_2mb'),
        ];
    }
}
