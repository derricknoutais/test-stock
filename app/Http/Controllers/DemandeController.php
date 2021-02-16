<?php

namespace App\Http\Controllers;

use App\Demande;
use App\Commande;
use App\Fournisseur;
use Illuminate\Http\Request;
use App\Exports\DemandeExport;
use App\Imports\DemandeImport;
use App\Jobs\GenererDemandes;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class DemandeController extends Controller
{

    public function index(Commande $commande)
    {
        $commande->loadMissing('demandes', 'demandes.sectionnables');
        return view('commande.demandes', compact('commande'));
    }

    public function dispatchSectionnables(Commande $commande)
    {

        dispatch(new GenererDemandes($commande));

    }

    public function destroySectionnable($id)
    {
        DB::table('demande_sectionnable')->where('id', $id)->delete();
    }

    public function store(Request $request)
    {
        $demande = Demande::create([
            'nom' => $request['fournisseur']['nom'],
            'commande_id' => $request['commande'],
            'fournisseur_id' => $request['fournisseur']['id']
        ]);
        return $demande;
    }

    public function show(Demande $demande)
    {
        $demande->loadMissing(['fournisseur', 'bonCommande' ,'sectionnables', 'sectionnables.product', 'sectionnables.product.handle', 'sectionnables.product.handle.brands' , 'sectionnables.article', 'sectionnables.bon_commande', 'sectionnables.demandes']);
        // return $demande->loadMissing(['sectionnables', 'sectionnables.product']);
        $demandes = Demande::all();
        return view('demande.show', compact('demande', 'demandes'));
    }

    public function showPrepaDemande(Commande $commande){

        $commande->loadMissing(['sections', 'sections.sectionnables', 'sections.sectionnables.demandes', 'sections.products', 'sections.articles', 'demandes', 'demandes.sectionnables', 'demandes.sectionnables.product']);
        $fournisseurs = Fournisseur::all();
        $commandes = Commande::all();

        return view('commande.prepa-demande', compact('commande', 'fournisseurs', 'commandes'));

    }

    public function addSectionnable(Request $request)
    {
        DB::table('demande_sectionnable')->insert([
            'demande_id' => $request['demandes']['id'],
            'sectionnable_id' => $request['products']['pivot']['id'],
            'offre' => 0,
            'quantite_offerte' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }


    public function export(Demande $demande)
    {
        return Excel::download(new DemandeExport($demande->id), 'RFQ ' . $demande->nom . ' '  . $demande->id . '-10-2020' . '.xlsx');
    }

    public function import(Request $request){
        // return $request->file('files');
        foreach($request->file('files') as $file){
            Excel::import(new DemandeImport, $file);
        }
    }
    public function updateProduct(Demande $demande, Request $request) {

        DB::table('demande_sectionnable')->where('id', $request['pivot']['id'])->update([
            'offre' => $request['pivot']['offre'],
            'quantite_offerte' => $request['pivot']['quantite_offerte'],
            'updated_at' => now()
        ]);

    }
    public function updateSectionnable(Request $request){
        $translation = '';

        if(isset($request['product']['handle']['translation'])){
            $translation = $request['product']['handle']['translation'];
        }
        if(isset($request['product']['handle']['display1']) && isset($request['product'])){
            $translation .=  ' / ' . $request['product'][$request['product']['handle']['display1']];
        }
        if(isset($request['product']['handle']['display2']) && isset($request['product'])){
            $translation .=  ' / ' . $request['product'][$request['product']['handle']['display2']];
        }
        if(isset($request['product']['handle']['display3']) && isset($request['product'])){
            $translation .=  ' / ' . $request['product'][$request['product']['handle']['display3']];
        }


        DB::table('demande_sectionnable')->where('id', $request['pivot']['id'])->update([
            'traduction' => $translation
        ]);
    }

    public function updateTraduction(Request $request){
        DB::table('demande_sectionnable')->where('id', $request['pivot']['id'])->update([
            'traduction' => $request['pivot']['traduction']
        ]);
    }

    public function patchSectionnable(Request $request){
        DB::table('demande_sectionnable')->where('id', $request['id'])->update([
            $request['field'] => $request['value']
        ]);
        return 1;
    }
}
