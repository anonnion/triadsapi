<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rules',
        'type',
    ];


    public function type(): String
    {
        return $this->getAttribute("type");
    }

    public function rules(): Object
    {
        return $this->getAttribute("rules");
    }
}
