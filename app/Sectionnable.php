<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Sectionnable extends Pivot
{
    protected $table= 'sectionnables';

    public function product()
    {
        return $this->belongsTo('App\Product', 'sectionnable_id');
    }
    public function section()
    {
        return $this->belongsTo('App\Section', 'section_id');
    }
}
