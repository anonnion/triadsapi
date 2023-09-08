<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class School extends Model
{
    use HasFactory;

    protected $fillables = [
        'name',
        'address',
        'country',
        'region',
        'state',
        'consent_approved',
        'is_currently_licensed',
    ];

    public function teams(): HasOneOrMany
    {
        return $this->hasMany(Team::class, 'school_id');
    }
}
