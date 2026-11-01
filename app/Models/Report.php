<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    protected $table = 'reports';

    protected $primaryKey = 'report_id';

    /** @var list<string> */
    protected $fillable = [
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

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id', 'user_id');
    }

    /** @return BelongsTo<Address, $this> */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }

    /** @return HasMany<ReportReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ReportReview::class, 'report_id', 'report_id');
    }

    /** @return HasMany<ReportForwarding, $this> */
    public function forwardings(): HasMany
    {
        return $this->hasMany(ReportForwarding::class, 'report_id', 'report_id');
    }
}
