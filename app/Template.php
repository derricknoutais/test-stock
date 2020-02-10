<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $guarded = [];
    
    public function products()
    {
        return $this->belongsToMany('App\Product')->withPivot('quantite');
    }

    public function commandes()
    {
        return $this->morphToMany('App\Commande', 'commandable');
    }
}
