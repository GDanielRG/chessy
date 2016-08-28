<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'key', 'color'

    ];

    public function users(){
        return $this->belongsToMany('App\User');
    }

    public function game(){
        return $this->HasOne('App\Game');
    }
}
