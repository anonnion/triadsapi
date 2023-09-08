<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $fillables = [
        'name',
        'dark_icon_url',
        'light_icon_url',
        'favicon_url',
    ];


}
