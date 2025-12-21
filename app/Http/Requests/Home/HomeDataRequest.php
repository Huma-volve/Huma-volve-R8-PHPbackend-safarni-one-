<?php

declare(strict_types=1);

namespace App\Http\Requests\Home;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for home page data endpoint.
 */
class HomeDataRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}