<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'logo_src',
        'banner_image_src',
        'rule_id',
        'linked_school_id',
    ];

    public function rules():HasOne
    {
        return $this->hasOne(GlobalRule::class, 'rule_id');
    }
}
