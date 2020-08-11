<?php

namespace App\Http\Controllers;

use DB;
use App\Facture;
use App\Product;
use App\Commande;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Commande $commande)
    {
        $commande->loadMissing(['bonsCommandes', 'bonsCommandes.sectionnables', 'bonsCommandes.sectionnables.product', 'factures', 'factures.sectionnables']);

        $factures = Facture::with('sectionnables')->latest()->get();

        return view('facture.index', compact('factures', 'commande'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Facture  $facture
     * @return \Illuminate\Http\Response
     */
    public function show(Facture $facture)
    {
        $facture->loadMissing('sectionnables', 'sectionnables.product');
        $products = Product::all();
        return view('facture.show', compact('facture', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Facture  $facture
     * @return \Illuminate\Http\Response
     */
    public function edit(Facture $facture)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Facture  $facture
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Facture $facture)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Facture  $facture
     * @return \Illuminate\Http\Response
     */
    public function destroy(Facture $facture)
    {
        //
    }

    public function updateSectionnable($sectionnable, Request $request){
        DB::table('facture_sectionnable')->where('id', $sectionnable)->update([
            'quantite' => $request['pivot']['quantite'],
            'prix_achat' => $request['pivot']['prix_achat']
        ]);
    }
    public function destroySectionnable($sectionnable){
        // return $sectionnable;
        DB::table('facture_sectionnable')->where('id', $sectionnable)->delete();
    }
}
