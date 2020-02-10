<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    protected $guarded = [];

    public function commande()
    {
        return $this->belongsTo('App\Commande');
    }
    public function sectionnables()
    {
        return $this->belongsToMany('App\Sectionnable', 'demande_sectionnable', 'demande_id', 'sectionnable_id')->withPivot('offre', 'id', 'checked');
    }
}
