<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $guarded = [];

    public function articles()
    {
        return $this->morphedByMany('App\Article', 'sectionnable');
    }
    public function products()
    {
        return $this->morphToMany('App\Product', 'sectionnable');
    }
    
}
