<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'game_id', 'user_id', 'team_id', 'move',
    ];

    public function teams(){
        return App\Team::whereIn('id', [$this->team_id1, $this->team_id2]);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    public function game()
    {
        return $this->belongsTo('App\Game');
    }
}
