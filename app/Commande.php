<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $guarded = [];

    public function products()
    {
        return $this->morphedByMany('App\Product', 'commandable');
    }

    public function sections()
    {
        return $this->morphedByMany('App\Section', 'commandable');
    }
    
    public function templates()
    {
        return $this->morphedByMany('App\Template', 'commandable');
    }

    public function reorderpoint()
    {
        return $this->morphedByMany('App\Reorderpoint', 'commandable');
    }
    public function demandes()
    {
        return $this->hasMany('App\Demande');
    }

}
