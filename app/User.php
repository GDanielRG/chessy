<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Game;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'facebook_key', 'slack_key', 'whatsapp_key', 'active_game'
    ];

    public function teams(){
        return $this->belongsToMany('App\Team');
    }

    public function activeGame(){
        return $this->hasOne('App\Game');
    }

    public function movements(){
        return $this->hasMany('App\Movement');
    }

    public function registerToGame($gameKey){
        $game= Game::where('key', $gameKey)->first();

        if(!$game || $game->started)
            return false;



    }

}
