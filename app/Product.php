<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    protected $guarded = [];
    protected $casts = ['id' => 'string'];
    public function commandes()
    {
        return $this->morphToMany('App\Commande', 'commandable');
    }
}
