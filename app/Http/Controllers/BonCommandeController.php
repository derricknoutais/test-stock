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

        $all = array();
        // Pour Chaque Demande d'Offre de Cette Commande...
        for (
            $i = 10; $i < 30; $i++
        ) {

            // Pour Chaque Sectionnable de Chaque Demande d'offre
            foreach($commande->demandes[$i]->sectionnables as $sectionnable){

                $commande->load('demandes', 'bonsCommandes');
                $commande->load('demandes.sectionnables');

                //  Si le produit n'a pas encore été checké
                if( $sectionnable->pivot->checked  == 0)
                {
                    unset($toCompare);
                    $toCompare = array();

                    // Ajoute le 1er produit dans notre liste à comparer
                    array_push($toCompare, $sectionnable);

                    // Pour toutes les autres demandes
                    for ($j=0; $j < sizeof($commande->demandes); $j++)
                    {
                        // Tant que ce n'est pas la meme demande dans laquelle est le produit
                        if($j != $i){
                            foreach($commande->demandes[$j]->sectionnables as $sectionnable_comparatif){
                                // Si le produit est identique au premier produit
                                if($sectionnable->sectionnable_id == $sectionnable_comparatif->sectionnable_id ){
                                    // Ajoute le à notre liste de produits a comparer
                                    array_push($toCompare, $sectionnable_comparatif);
                                }
                            }
                        }
                    }


                    // Classe les produits dans notre liste à comparer du moins cher au plus
                    usort($toCompare, function( $a, $b) {
                        // Pour deux produits au prix identiques
                        if($a->pivot->offre == $b->pivot->offre ){
                            $a->pivot->checked = -1;
                            $b->pivot->checked = -1;
                            DB::table('demande_sectionnable')
                                ->whereIn('id', [$b->pivot->id, $a->pivot->id])
                                ->update([
                                    'checked' => -1
                                ]);
                            DB::table('sectionnables')->where([ 'section_id' => $a->section_id, 'sectionnable_id' => $a->sectionnable_id ])->update([
                                'conflit' => 1
                            ]);
                        // Pour deux produits de prix inférieurs
                        } else {
                            $a->pivot->checked = 1;
                            $b->pivot->checked = 1;
                            DB::table('demande_sectionnable')
                                ->whereIn('id', [$b->pivot->id, $a->pivot->id])
                                ->update([
                                    'checked' => 1
                                ]);
                        }
                        return $a['pivot']['offre'] <=> $b['pivot']['offre'];
                    });

                    foreach ($toCompare as $comp) {
                        if($comp->pivot->offre <= 0 || $comp->pivot->differente_offre){
                            DB::table('demande_sectionnable')
                            ->where('id', $comp->pivot->id)
                            ->update([
                                'checked' => -1
                            ]);
                            $comp->pivot->checked = -1;
                            DB::table('sectionnables')->where([ 'section_id' => $comp->section_id, 'sectionnable_id' => $comp->sectionnable_id ])->update([
                                'conflit' => 1
                            ]);
                        }
                    }
                    // return $toCompare;
                    $qte_recevable = $toCompare[0]->quantite;
                    $x = 0;
                    while ( $qte_recevable > 0 ) {
                        // Si la quantité a recevoir  est supérieure a la quantite offerte par le fournisseur x

                        if( ( $qte_recevable - $toCompare[$x]->pivot->quantite_offerte )  > 0 ){

                            // Prennons tout ce que le fournisseur nous offre
                            $toCompare[$x]->quantite_prise = $toCompare[$x]->pivot->quantite_offerte;

                            // Puis passons au fournisseur suivant si il en reste
                            if( ($x + 1) < sizeof($toCompare) ){
                                $x++;
                            } else {
                                // Sinon on stoppe
                                break;
                            }
                        }
                        // Si la quantité a recevoir est inférieure a la quantite offerte par le fournisseur x
                        else {
                            // On prend seulement la quantité restante et on stoppe l'exectution
                            $toCompare[$x]->quantite_prise = $qte_recevable ;
                            break;
                        }

                        // On soustrait la quantité prise chez le fournisseur x de notre total
                        if($x > 0){
                            $qte_recevable = $qte_recevable - $toCompare[$x-1]->quantite_prise;
                        } else {
                            $qte_recevable = $qte_recevable - $toCompare[$x]->quantite_prise;
                        }

                        // Stoppe la boucle si nous sommes au bout de notre tableau de fournisseur
                        if( $x >= sizeof($toCompare)  ) {
                            break;
                        }

                    }
                    // return $x;


                    for ($y=0; $y <= $x ; $y++) {
                        if( $toCompare[$y]->pivot->checked !== -1 ){
                            if( $bc = BonCommande::where('demande_id' , $toCompare[$y]->pivot->demande_id )->first() )
                            {
                                DB::table('bon_commande_sectionnable')->insert([
                                    'bon_commande_id' => $bc->id,
                                    'sectionnable_id' => $toCompare[$y]->id,
                                    'quantite' => $toCompare[$y]->quantite_prise,
                                    'prix_achat' => $toCompare[$y]->pivot->offre
                                ]);
                            }
                            else
                            {
                                $demande = Demande::find($toCompare[$y]->pivot->demande_id);
                                $bc = BonCommande::create([
                                    'commande_id' => $commande->id,
                                    'nom' => 'Bon Commande ' . $demande->nom ,
                                    'demande_id' => $toCompare[$y]->pivot->demande_id
                                ]);
                                DB::table('bon_commande_sectionnable')->insert([
                                    'bon_commande_id' => $bc->id,
                                    'sectionnable_id' => $toCompare[$y]->id,
                                    'quantite' => $toCompare[$y]->quantite_prise,
                                    'prix_achat' => $toCompare[$y]->pivot->offre
                                ]);
                            }
                            DB::table('demande_sectionnable')
                            ->where('id', $toCompare[$y]->pivot->id)
                            ->update([
                                'checked' => 1
                            ]);
                            $toCompare[$y]->pivot->checked = 1;
                        }
                    }
                }
            }
        }
        return 'Done';
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
        return Excel::download(new BCommandeExport($bonCommande->id), $bonCommande->nom . '.xlsx');
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
            'nom' => $bc->nom,
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
