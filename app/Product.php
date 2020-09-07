<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    protected $guarded = [];
    protected $casts = ['id' => 'string'];
    public $timestamps = false;

    public function commandes()
    {
        return $this->morphToMany('App\Commande', 'commandable');
    }
    public function demande()
    {
        return $this->morphToMany('App\Demande', 'demande_sectionnable');
    }
    public function fournisseurs()
    {
        return $this->belongsToMany('App\Fournisseur', 'product_fournisseur');
    }
    public function handle()
    {
        return $this->belongsTo('App\Handle');
    }
}
