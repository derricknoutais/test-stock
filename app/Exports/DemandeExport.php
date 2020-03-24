<?php

namespace App\Exports;

use App\Demande;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DemandeExport implements FromView
{
    protected $demande_id;

    public function __construct($demande_id){
        $this->demande_id = $demande_id;
    }
    public function view(): View
    {
        return view('exports.demandes', [ 'demande' => Demande::where('id', $this->demande_id)->with('sectionnables', 'sectionnables.product', 'sectionnables.article')->first() ]);
    }
}
