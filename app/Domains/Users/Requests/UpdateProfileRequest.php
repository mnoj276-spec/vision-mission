<?php

namespace App\Domains\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = Auth::id();
        return [
            'name'     => 'required|string|max:255',
            'email'    => "required|string|email|max:255|unique:users,email,{$userId}",
            'phone'    => "required|string|max:15|unique:users,phone,{$userId}",
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }
}
