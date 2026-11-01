<?php

namespace App\Http\Requests;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('report');
        $user = $this->user();

        return $report instanceof Report
            && $user instanceof User
            && $user->can('reviewAsVolunteer', $report);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'review_note' => ['required', 'string', 'max:10000'],
            'followed_up_required' => ['sometimes', 'boolean'],
        ];
    }
}
