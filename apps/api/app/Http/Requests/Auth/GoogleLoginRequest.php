<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class GoogleLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // OAuth access token the SPA obtained from Google's client-side flow.
            'access_token' => ['required', 'string'],
        ];
    }
}
