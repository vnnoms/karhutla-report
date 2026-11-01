<?php

namespace App\Http\Requests;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportForwardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('report');
        $user = $this->user();

        return $report instanceof Report
            && $user instanceof User
            && $user->can('forwardAsCoordinator', $report);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'destination_name' => ['required', 'string', 'max:150'],
            'forwarding_note' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
