<?php

namespace App\Http\Requests;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $report = $this->route('report');
        $user = $this->user();

        return $user instanceof User
            && $user->role === 'reporter'
            && $report instanceof Report
            && (int) $report->reporter_id === (int) $user->getAuthIdentifier()
            && $report->status === 'pending';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'report_description' => ['required', 'string', 'max:10000'],
            'incident_time' => ['required', 'date_format:Y-m-d\\TH:i'],
            'condition_level' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'critical'])],
            'detail_address' => ['required', 'string', 'max:10000'],
            'city_regency' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'island' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'evidence_file' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }
}
