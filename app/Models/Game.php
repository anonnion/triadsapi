<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    /**
     * @definition
     * @for Game.available_platforms
     * @dataType json
     * @structure
     * {
     *  array (
     *   'id' => 'GAME_ID',
     *   'tournaments' => 'tournament_ids',
     *   'matches' => 'match_ids',
     *   'mvps' => 'mvp_count',
     *  )
     * }
     */

    protected $fillables = [
        'name',
        'available_platforms',
    ];

    public function platforms()
    {
        $data = [];
        $platforms = $this->getAttribute("available_platforms");
        foreach($platforms as $platform)
        {
            if($platform->available == true)
            {
                $details = Platform::find($platform->id);
                $data[$details->name] = $details;
            }
        }
        $this->attributes->set('platforms', $data);
        return $data;
    }
}
