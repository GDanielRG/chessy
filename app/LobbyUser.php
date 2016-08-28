<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LobbyUser extends Model
{

    protected $table = "game_user";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id',
    ];

}
