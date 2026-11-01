<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User && $this->user()->role === 'reporter';
    }

    public function messages(): array
    {
        return [
            'report_description' => 'Jumlah karakter melebihi batas maksimal.',
            'incident_time' => 'Format waktu kejadian tidak valid.',
            'condition_level' => 'Tingkat kondisi tidak valid.',
            'detail_address' => 'Alamat detail wajib diisi.',
            'city_regency' => 'Kota/Kabupaten wajib diisi.',
            'province' => 'Provinsi wajib diisi.',
            'island' => 'Pulau wajib diisi.',
            'evidence_file.extensions' => 'Format file bukti kejadian harus JPG, JPEG, atau PNG.',
        ];
    }

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
                'mimes:jpg,jpeg,png',
                'extensions:jpg,jpeg,png',
                'max:2048',
            ],
        ];
    }
}
