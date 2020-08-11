<?php

namespace App\Http\Controllers;

use DB;
use App\Facture;
use App\Product;
use App\Commande;
use App\BonCommande;
use App\Section;
use App\Sectionnable;
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
        // return  BonCommande::with('fournisseur')->get();
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
    public function storeSectionnable(Request $request){
        //
        $section = Section::where([ 'commande_id' => $request['bc']['commande_id'], 'nom' => '***Retard***' ])->first();

        if($section){
            // Crée le Sectionnable
            $sectionnable = Sectionnable::create([
                'section_id' => $section->id,
                'sectionnable_id' => $request['product']['id'],
                'sectionnable_type' => 'App\Product',
                'quantite' => $request['product']['quantite'],
                'created_at' => now(),
                'updated_at' => now(),
                'conflit' => 0
            ]);
            // Insère le sectionnable au bon de commande

            DB::table('facture_sectionnable')->insert([
                'sectionnable_id' => $sectionnable->id,
                'facture_id' => $request['bc']['id'],
                'quantite' => $request['product']['quantite'],
                'prix_achat' => $request['product']['prix_achat'],
                'created_at' => now(),
                'updated_at' => now()
            ]);


        } else {
            $section = Section::create([
                'commande_id' => $request['bc']['commande_id'],
                'nom' => '***Retard***',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            // Crée le Sectionnable
            $sectionnable = Sectionnable::create([
                'section_id' => $section->id,
                'sectionnable_id' => $request['product']['id'],
                'sectionnable_type' => 'App\Product',
                'quantite' => $request['product']['quantite'],
                'created_at' => now(),
                'updated_at' => now(),
                'conflit' => 0
            ]);
            // Insère le sectionnable au bon de commande
            DB::table('facture_sectionnable')->insert([
                'sectionnable_id' => $sectionnable->id,
                'facture_id' => $request['bc']['id'],
                'quantite' => $request['product']['quantite'],
                'prix_achat' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

        }

        return $sectionnable;
    }
}