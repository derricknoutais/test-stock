<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany('App\Product')->withPivot('section', 'id', 'quantity');
    }
    
    public function templates()
    {
        return $this->morphedByMany('App\Template', 'commandable');
    }

    public function reorderpoint()
    {
        return $this->morphedByMany('App\Reorderpoint', 'commandable');
    }

}
