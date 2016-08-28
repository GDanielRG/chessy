<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{

    protected $table = "team_user";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'team_id',
    ];
}
