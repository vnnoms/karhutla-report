<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportForwarding extends Model
{
    protected $table = 'report_forwardings';

    protected $primaryKey = 'forwarding_id';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'report_id',
        'coordinator_id',
        'destination_name',
        'forwarding_note',
        'forwarded_at',
    ];

    /** @return BelongsTo<Report, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id', 'report_id');
    }

    /** @return BelongsTo<User, $this> */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id', 'user_id');
    }
}
