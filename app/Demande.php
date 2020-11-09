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
        return $this->belongsToMany('App\Sectionnable', 'demande_sectionnable', 'demande_id', 'sectionnable_id')->withPivot('quantite', 'offre', 'id', 'checked', 'quantite_offerte', 'traduction', 'differente_offre', 'reference_differente_offre');
    }
}
