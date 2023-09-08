<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameData extends Model
{
    use HasFactory;
    /**
     * @definition
     * @for GameData.data
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
        'user_id',
        'data',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function games()
    {
        $data = [];
        $games = $this->getAttribute("data");
        foreach($games as $game)
        {
            if($game->available == true)
            {
                $details = Game::find($game->id);
                $data[$details->name] = $details;
            }
        }
        $this->attributes->set('games', $data);
        return $data;
    }
}
