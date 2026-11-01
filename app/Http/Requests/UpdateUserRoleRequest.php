<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $targetUser = $this->route('user');
        $admin = $this->user();

        return $targetUser instanceof User
            && $admin instanceof User
            && $admin->can('updateRole', $targetUser);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(['reporter', 'volunteer', 'coordinator'])],
        ];
    }
}
