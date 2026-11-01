<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id', 'user_id');
    }

    public function volunteerReviews(): HasMany
    {
        return $this->hasMany(ReportReview::class, 'volunteer_id', 'user_id');
    }

    public function coordinatorForwardings(): HasMany
    {
        return $this->hasMany(ReportForwarding::class, 'coordinator_id', 'user_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
