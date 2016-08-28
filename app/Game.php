<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator', 'team_id1', 'team_id2', 'turn', 'ended', 'started', 'key'
    ];

    public function teams(){
        return App\Team::whereIn('id', [$this->team_id1, $this->team_id2]);
    }
}
