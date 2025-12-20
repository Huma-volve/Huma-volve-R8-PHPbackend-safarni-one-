<?php

declare(strict_types=1);

namespace App\Http\Requests\Seat;

use Illuminate\Foundation\Http\FormRequest;

class LockSeatRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seat_id' => ['required', 'uuid', 'exists:seats,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'seat_id.required' => 'Please provide a seat ID.',
            'seat_id.uuid' => 'Invalid seat ID format.',
            'seat_id.exists' => 'Seat not found.',
        ];
    }
}