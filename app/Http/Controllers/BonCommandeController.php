<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 1800);
use DB;
use App\BonCommande;
use App\Demande;
use App\Commande;
use App\Product;
use App\Section;
use App\Facture;
use App\Sectionnable;
use App\Exports\BCommandeExport;
use App\Jobs\GenererBons;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;

class BonCommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Commande $commande)
    {
        $commande->loadMissing(['bonsCommandes', 'bonsCommandes.sectionnables' ,'bonsCommandes.sectionnables.product']);
        return view('commande.bon-commandes', compact('commande'));
    }

    public function show(Commande $commande, BonCommande $bc){
        $commande->loadMissing(['bonsCommandes', 'bonsCommandes.sectionnables', 'bonsCommandes.sectionnables.product']);
        $bc->loadMissing('sectionnables', 'sectionnables.product', 'sectionnables.article' );
        $products = Product::all();
        return view('commande.bon_commande_show', compact('commande', 'bc', 'products'));
    }
    public function updateSectionnable($sectionnable, Request $request){
        DB::table('bon_commande_sectionnable')->where('id', $sectionnable)->update([
            'quantite' => $request['pivot']['quantite'],
            'prix_achat' => $request['pivot']['prix_achat']
        ]);
    }
    public function updateAllSectionnable( Request $request ){
        return $request->all();
    }

    public function générerBons(Commande $commande){
        $job1 = new GenererBons($commande, 0, 5);
        $job2 = new GenererBons($commande,5, 10);

        $job3 = new GenererBons($commande, 10, 15);
        $job4 = new GenererBons($commande, 15, 20);
        dispatch($job1)->onQueue('test1');
        dispatch($job2)->onQueue('test2');
        dispatch($job3)->onQueue('test3');
        dispatch($job4)->onQueue('test4');
        // dispatch($job4);
    }

    public function showConflit(Commande $commande){

            $commande->load('demandes', 'bonsCommandes', 'demandes.sectionnables', 'sections', 'sections.articles', 'sections.products');
            $conflits = array();
            foreach ($commande->sections as $section ) {

                foreach ($section->articles as $article ) {
                    if($article->pivot->conflit === 1 ){
                        array_push($conflits, $article);
                    }
                }
                foreach ($section->products as $product ) {
                    if($product->pivot->conflit === 1 ){
                        array_push($conflits, $product);
                    }
                }
            }
            foreach( $conflits as $conflit ){
                $conflit->elements_conflictuels = DB::table('demande_sectionnable')->where( 'sectionnable_id', $conflit->pivot->id )->get();
            }
            $commande->conflits = $conflits;
            return view('commande.conflits', compact('commande', 'conflits'));
    }

    public function export(BonCommande $bonCommande)
    {
        return Excel::download(new BCommandeExport($bonCommande->id), $bonCommande->nom . '-porto.xlsx');
    }

    public function exportall(Commande $commande){
        $commande->loadMissing('bonsCommandes');
        foreach ($commande->bonsCommandes as $bc) {
            $this->export( $bc );
        }
    }

    public function storeSectionnable(Request $request){
        //
        $sectionnables = Sectionnable::whereIn('section_id', Section::where('commande_id', $request['bc']['commande_id'])->pluck('id'))->get()->toArray();
        $array = array_filter($sectionnables, function($sectionnable) use ($request){
            return $sectionnable['sectionnable_id'] === $request['product']['id'];
        });
        if( isset($array) ){
            //
            $section = Section::where([ 'commande_id' => $request['bc']['commande_id'], 'nom' => '***Retard***' ])->first();
            //
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

                DB::table('bon_commande_sectionnable')->insert([
                    'sectionnable_id' => $sectionnable->id,
                    'bon_commande_id' => $request['bc']['id'],
                    'quantite' => $request['product']['quantite'],
                    'prix_achat' => 0,
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
                DB::table('bon_commande_sectionnable')->insert([
                    'sectionnable_id' => $sectionnable->id,
                    'bon_commande_id' => $request['bc']['id'],
                    'quantite' => $request['product']['quantite'],
                    'prix_achat' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            }
        } else  {
            DB::table('bon_commande_sectionnable')->insert([
                'sectionnable_id' => $array['sectionnable_id'],
                'bon_commande_id' => $request['bc']['id'],
                'quantite' => $request['product']['quantite'],
                'prix_achat' => $array['offre'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $sectionnable;
    }

    public function destroySectionnable($sectionnable){
        DB::table('bon_commande_sectionnable')->where('id', $sectionnable)->delete();
    }


    public function createInvoice(BonCommande $bc ){
        $facture = Facture::create([
            'nom' => 'Facture ' . $bc->nom,
            'commande_id' => $bc->commande_id,
            'demande_id' => $bc->demande_id,
            'fournisseur_id' => $bc->fournisseur_id,
            'bon_commande_id' => $bc->id
        ]);
        $bc->update([
            'facture_id' => $facture->id
        ]);

        $sectionnables = DB::table('bon_commande_sectionnable')->where('bon_commande_id', $bc->id)->get();

        foreach ($sectionnables as $sectionnable ) {
            DB::table('facture_sectionnable')->insert([
                'sectionnable_id' => $sectionnable->sectionnable_id,
                'facture_id' => $facture->id,
                'quantite' => $sectionnable->quantite,
                'prix_achat' => $sectionnable->prix_achat
            ]);
        }

        return $facture->id;



    }
}
