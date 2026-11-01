<?php

namespace App\Observers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportObserver
{
    /** @var list<string> */
    private const AUDITED_FIELDS = [
        'reporter_id',
        'evidence_file',
        'report_description',
        'incident_time',
        'condition_level',
        'address_id',
        'latitude',
        'longitude',
        'status',
    ];

    public function created(Report $report): void
    {
        $this->logAfterCommit('report.created', [
            'report_id' => $report->getKey(),
            'reporter_id' => $report->reporter_id,
            'actor_user_id' => Auth::id(),
            'status' => $report->status,
            'changed_fields' => array_values(array_intersect(array_keys($report->getAttributes()), self::AUDITED_FIELDS)),
        ]);
    }

    public function updated(Report $report): void
    {
        $changedFields = array_values(array_intersect(array_keys($report->getChanges()), self::AUDITED_FIELDS));

        if ($changedFields === []) {
            return;
        }

        $context = [
            'report_id' => $report->getKey(),
            'reporter_id' => $report->reporter_id,
            'actor_user_id' => Auth::id(),
            'changed_fields' => $changedFields,
        ];

        if (in_array('status', $changedFields, true)) {
            $context['previous_status'] = $report->getOriginal('status');
            $context['status'] = $report->status;
        }

        $this->logAfterCommit('report.updated', $context);
    }

    public function deleted(Report $report): void
    {
        $this->logAfterCommit('report.deleted', [
            'report_id' => $report->getKey(),
            'reporter_id' => $report->reporter_id,
            'actor_user_id' => Auth::id(),
            'status' => $report->status,
        ]);
    }

    /** @param array<string, mixed> $context */
    private function logAfterCommit(string $event, array $context): void
    {
        DB::afterCommit(fn () => Log::notice($event, $context));
    }
}
