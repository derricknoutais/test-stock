<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BonCommande extends Model
{
    protected $guarded = [];

    public function sectionnables()
    {
        return $this->belongsToMany('App\Sectionnable', 'bon_commande_sectionnable', 'bon_commande_id', 'sectionnable_id')->withPivot('prix_achat', 'quantite');
    }
}
