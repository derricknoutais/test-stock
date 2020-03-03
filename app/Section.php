<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $guarded = [];

    public function articles()
    {
        return $this->morphedByMany('App\Article', 'sectionnable')->withPivot('id', 'quantite', 'conflit');
    }
    public function products()
    {
        return $this->morphedByMany('App\Product', 'sectionnable')->withPivot('id', 'quantite','conflit');
    }
    public function sectionnables(){
        return $this->articles()->union($this->products()->toBase());
    }
}
