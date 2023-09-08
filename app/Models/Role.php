<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Role extends Model
{
    use HasFactory;

    protected $fillables = [
        "for",
        "for_id",
        "user_id",
        "permission",
    ];

    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class, "permission");
    }

    public function user(): BelongsToMany

    {
        return $this->belongsToMany(User::class, "user_id");
    }
}
