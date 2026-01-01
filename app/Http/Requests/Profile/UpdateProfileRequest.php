<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+( [a-zA-Z0-9\-_]+)*$/',
            ],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
                'regex:/^[a-z0-9._%+-]+@gmail\.com$/',
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^(\+20|0)1[0125][0-9]{8}$/', 'unique:users,phone,' . $userId, 'min:11', 'max:13'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'profile_image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'Name must be at least 3 characters.',
            'name.regex' => 'Name must not start or end with spaces, allow only single spaces between words, and can only contain letters, numbers, dashes, and underscores.',
            'email.regex' => 'Email must be a valid lowercase @gmail.com address.',
            'phone.regex' => 'Phone number must be a valid Egyptian mobile number (e.g., 01xxxxxxxxx or +201xxxxxxxxx).',
            'profile_image.image' => 'Profile image must be a valid image file.',
            'profile_image.mimes' => 'Profile image must be a JPEG, PNG, JPG, or GIF.',
            'profile_image.max' => 'Profile image size must not exceed 2MB.',
        ];
    }
}
