<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportReview extends Model
{
    protected $table = 'report_reviews';

    protected $primaryKey = 'review_id';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'report_id',
        'volunteer_id',
        'review_note',
        'review_date',
        'followed_up_required',
    ];

    /** @return BelongsTo<Report, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id', 'report_id');
    }

    /** @return BelongsTo<User, $this> */
    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id', 'user_id');
    }
}
