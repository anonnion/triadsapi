<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

use App\Models\GlobalRule;
use App\Models\Community;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'community_id',
        'school_id',
        'type',
        'logo_src',
        'banner_image_src',
        'rule_id',
    ];

    public function rules():HasOne
    {
        return $this->hasOne(GlobalRule::class, 'rule_id');
    }

    public function community():BelongsTo
    {
        return $this->belongsTo(Community::class, 'community_id');
    }

    public function team():BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
