<?php

namespace App\Http\Controllers;

use App\Demande;
use App\Commande;
use App\Fournisseur;
use Illuminate\Http\Request;
use App\Exports\DemandeExport;
use App\Imports\DemandeImport;
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
        $commande->loadMissing('sections', 'sections.sectionnables', 'sections.sectionnables.product', 'sections.sectionnables.product.fournisseurs');
        /***
            Pour chaque section de la commande
            Exemple : Toyota C... / Avensis / Joints / Nouveaux Produits sont des sections
        */
        foreach($commande->sections as $section){
            /***
                Chaque Section a des sectionnables. Les sectionnables sont des éléments qui appartiennent à des sections
                Il existe 2 types de sectionnables : Les Produits Vend & Les Articles Demandées par les clients dans Fiche de Renseignement
                Donc Pour chaque sectionnable de chaque Section
            */
            foreach($section->sectionnables as $sectionnable){
                if($sectionnable->sectionnable_type === 'App\\Product'){
                    if(isset($sectionnable->product->fournisseurs)){
                        foreach($sectionnable->product->fournisseurs as $fournisseur){
                            if($demande = Demande::where( ['fournisseur_id' => $fournisseur->id, 'commande_id' => $commande->id])->first() ){
                                DB::table('demande_sectionnable')->insert([
                                    'sectionnable_id' => $sectionnable->id,
                                    'demande_id' => $demande->id,
                                    'offre' => 0,
                                    'quantite_offerte' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                    // 'offre' => rand(1, 9) * 1000,
                                    // 'quantite_offerte' => round( (rand(1, 10)/10) * $sectionnable->quantite)
                                ]);
                            } else {
                                $demande = Demande::create([
                                    'nom' => $fournisseur->nom,
                                    'commande_id' => $commande->id,
                                    'fournisseur_id' => $fournisseur->id
                                ]);
                                DB::table('demande_sectionnable')->insert([
                                    'sectionnable_id' => $sectionnable->id,
                                    'demande_id' => $demande->id,
                                    'offre' => 0,
                                    'quantite_offerte' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                    // 'offre' => rand(1, 9) * 1000,
                                    // 'quantite_offerte' => round( (rand(1, 10)/10) * $sectionnable->quantite)
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return 'OK';
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
        $demande->loadMissing(['sectionnables', 'sectionnables.product', 'sectionnables.product.handle' , 'sectionnables.article']);
        // return $demande->loadMissing(['sectionnables', 'sectionnables.product']);
        return view('demande.show', compact('demande'));
    }

    public function showPrepaDemande(Commande $commande){

        $commande->loadMissing(['sections', 'sections.sectionnables', 'sections.sectionnables.demandes', 'sections.products', 'sections.articles', 'demandes', 'demandes.sectionnables', 'demandes.sectionnables.product']);
        $fournisseurs = Fournisseur::all();

        return view('commande.prepa-demande', compact('commande', 'fournisseurs'));

    }

    public function addSectionnable(Request $request)
    {
        DB::table('demande_sectionnable')->insert([
            'demande_id' => $request['demandes']['id'],
            'sectionnable_id' => $request['products']['pivot']['id'],
            'offre' => 0,
            'quantite_offerte' => 0
        ]);
    }


    public function export(Demande $demande)
    {
        return Excel::download(new DemandeExport($demande->id), $demande->nom . '.xlsx');
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
            'quantite_offerte' => $request['pivot']['quantite_offerte']
        ]);

    }
}
