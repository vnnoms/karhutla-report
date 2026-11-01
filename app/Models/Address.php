<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    protected $table = 'addresses';
    protected $primaryKey = 'address_id';
    public $timestamps = false;

    protected $fillable = [
        'detail_address',
        'city_regency',
        'province',
        'island',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'address_id', 'address_id');
    }
}
