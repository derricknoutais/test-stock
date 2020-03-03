<?php

// use DB;
use App\Product;
use App\Commande;
use App\BonCommande;
use App\Demande;
use App\Fournisseur;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('accueil');
});

Route::resource('/product', 'ProductController');

Route::resource('/template', 'TemplateController');

Route::resource('/commande', 'CommandeController');

Route::resource('/section', 'SectionController');

Route::resource('/fournisseur', 'FournisseurController');

Route::delete('/sectionnable/{product}', 'SectionController@destroyProduct');
Route::delete('/sectionnable/{article}', 'SectionController@destroyArticle');

Route::resource('/demande', 'DemandeController');

 
Route::get('/commande/{commande}/prepa-demande', function(Commande $commande){
    $commande->loadMissing(['sections', 'sections.products', 'sections.articles', 'demandes', 'demandes.sectionnables', 'demandes.sectionnables.product']);
    $fournisseurs = Fournisseur::all();
    return view('commande.prepa-demande', compact('commande', 'fournisseurs'));
});

Route::get('/commande/{commande}/demandes', function(Commande $commande){
    $commande->loadMissing('demandes', 'demandes.sectionnables');
    return view('commande.demandes', compact('commande'));
});

Route::get('/remettre-a-zero', function(){
    DB::table('demande_sectionnable')->whereIn('demande_id', [4,5,6,7,8])->update([
        'checked' => 0
    ]);
    $bcs = DB::table('bon_commandes')->whereIn('demande_id', [4,5,6,7,8])->pluck('id');
    DB::table('bon_commande_sectionnable')->whereIn('bon_commande_id', $bcs)->delete();
    DB::table('bon_commandes')->whereIn('demande_id', [4,5,6,7,8])->delete();
});

Route::get('/commande/{commande}/générer-bons', function(Commande $commande){

    $all = array();
    // Pour Chaque Demande d'Offre de Cette Commande... 
    for ( $i = 0; $i < sizeof($commande->demandes); $i++ ) {
        
        // Pour Chaque Section de Chaque Demande
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
                    if($a->pivot->offre == $b->pivot->offre){
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
                    if($comp->pivot->offre == 0){
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

                    if( ( $qte_recevable - $toCompare[$x]->pivot->quantite_offerte )  > 0 ){
                        $toCompare[$x]->quantite_prise = $toCompare[$x]->pivot->quantite_offerte;
                        if( ($x + 1) < sizeof($toCompare) ){
                            $x++;
                        } else {
                            break;
                        }
                    } 
                    else {
                        $toCompare[$x]->quantite_prise = $qte_recevable ;
                        break;
                    }

                    if($x > 0){
                        $qte_recevable = $qte_recevable - $toCompare[$x-1]->quantite_prise;
                    } else {
                        $qte_recevable = $qte_recevable - $toCompare[$x]->quantite_prise;
                    }
                    

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
});

Route::get('/commande/{commande}/conflits', function(Commande $commande){
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
});

Route::post('/commande/{commande}/résoudre-conflit', function(Commande $commande, Request $request){
    // return $request->all();

        if( $bc = BonCommande::where('demande_id' , $request['selected']['demande_id'] )->first() )
        {
            DB::table('bon_commande_sectionnable')->insert([
                'bon_commande_id' => $bc->id,
                'sectionnable_id' => $request['selected']['id'],
                'quantite' => $request['selected']['quantite_offerte'],
                'prix_achat' => $request['selected']['pivot']['offre']
            ]);
        } 
        else
        {
            $bc = BonCommande::create([
                'commande_id' => $commande->id,
                'nom' => 'Bon Commande ' . $request['selected']['demande']['nom'] ,
                'demande_id' => $request['selected']['demande_id']
            ]);
            DB::table('bon_commande_sectionnable')->insert([
                'bon_commande_id' => $bc->id,
                'sectionnable_id' => $request['selected']['id'],
                'quantite' => $request['selected']['quantite_offerte'],
                'prix_achat' => $request['selected']['offre']
            ]);
        }
        foreach( $request['elements_conflictuels'] as $element){
            DB::table('demande_sectionnable')
            ->where('id', $element['id'])
            ->update([
                'checked' => 1
            ]);
        }
        DB::table('sectionnables')
        ->where('id', $request['pivot']['id'])
        ->update([
            'conflit' => 0
        ]);
});

Route::get('/commande/{commande}/bons-commandes', function(Commande $commande){
    $commande->loadMissing(['bonsCommandes', 'bonsCommandes.sectionnables' ,'bonsCommandes.sectionnables.product']);
    return view('commande.bon-commandes', compact('commande'));
});
Route::get('/commande/{commande}/bons-commandes/{bc}', function (Commande $commande, BonCommande $bc) {
    $commande->loadMissing(['bonsCommandes', 'bonsCommandes.sectionnables', 'bonsCommandes.sectionnables.product']);
    $bc->loadMissing('sectionnables', 'sectionnables.product', 'sectionnables.article' );
    return view('commande.bon_commande_show', compact('commande', 'bc'));
});


Route::post('/demande-sectionnable', function(Request $request){
    // return $request->all();
    foreach ($request['demandes'] as $demande ) {
        foreach ($request['products'] as $product) {
            DB::table('demande_sectionnable')->insert([
                'demande_id' => $demande['id'],
                'sectionnable_id' => $product['pivot']['id'],
                'offre' => 0,
                'quantite_offerte' => 0
            ]); 
        }
    }
});
Route::delete('demande-sectionnable/{id}', function ($id) {
    DB::table('demande_sectionnable')->where('id', $id)->delete();
});

Route::put('demande/{demande}/update-product', function (Demande $demande, Request $request) {

    if($request['pivot']['quantite_offerte'] < $request['quantite']){

    }
    DB::table('demande_sectionnable')->where('id', $request['pivot']['id'])->update([
        'offre' => $request['pivot']['offre'],
        'quantite_offerte' => $request['pivot']['quantite_offerte']
    ]);

});
Route::get('/kd', function(){
    return App\Sectionnable::all();
});


Route::post('/product-section', 'SectionController@addProduct');


Route::post('/product-template', 'ProductTemplateController@addProduct');
Route::post('/product-template/delete', 'ProductTemplateController@removeProduct');

Route::post('/product-commande', 'CommandeController@addProduct');
Route::post('/template-commande', 'CommandeController@addTemplate');




Route::post('/reorderpoint-commande', 'CommandeController@addReorderPoint');



Route::get('/test','CommandeController@consignment');

Route::get('/test', 'CommandeController@addReorderPoint');

Route::post('/commande-quantité', 'CommandeController@addQuantities');


Route::get('/t', function(){
    $produits = App\Product::where('handle', 'PlateauDembrayageAisin')->get();
    return view('work', compact('produits'));
});



// /section-product/delete/' + article.id + '/' + section.id

Route::get('/section-product/delete/{article}/{section}', function($article, $section){
    $var = DB::table('sectionnables')->where(['section_id' => $section, 'sectionnable_id' => $article])->delete();
    return $var;
});

Route::put('product-template', function ( Request $request) {
    foreach ($request->all() as $prodTemp) {
        DB::table('product_template')->where(['template_id' => $prodTemp['template_id'] , 'product_id' => $prodTemp['product_id']])->update([
            'quantite' => $prodTemp['quantite']
        ]);
    }
    return $request->all();
});


Route::put('article-update', function( Request $request){
    // return $request->all();
    $art = DB::table('sectionnables')->where('id', $request['article']['pivot']['id'])->update([
            'quantite' => $request['article']['pivot']['quantite']
        ]);
});


Route::get('/api/produits', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
        'Accept'        => 'application/json',
    ];

    $pages = array();

    for ($j = 1; $j <= 1; $j++) {

        $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
        $data = json_decode((string) $response->getBody(), true);
        array_push($pages, $data['products']);

    }
    foreach ($pages as $products) {
        foreach ($products as $product) {
            if (!Product::where('id', $product['id'])->first()) {
                Product::create([
                    'id' => $product['id'],
                    'handle' => $product['handle'],
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'price' => $product['price'],
                    'supply_price' => $product['supply_price']
                ]);
            }
        }
    }
    

    // return $totalProducts;
});





























































Route::get('/api/stock', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
        'Accept'        => 'application/json',
    ];
    for ($j = 1; $j <= 17; $j++) {

        $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/products?page_size=200', ['headers' => $headers]);
        $data = json_decode((string) $response->getBody(), true);
        return $data['data'];
    }
        foreach ($products as $product) {
            if (!Product::find($product['id'])) {

                Product::create([
                    'id' => $product['id'],
                    'handle' => $product['handle'],
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'price' => $product['price'],
                    'supply_price' => $product['supply_price']
                ]);
            }
        }   


    // return $totalProducts;
});










Route::get('/api/products', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
        'Accept'        => 'application/json',
    ];

    $pages = array();

    for ($j = 17; $j <= 18; $j++) {

        $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
        $data = json_decode((string) $response->getBody(), true);
        array_push($pages, $data['products']);
    }
    $push = [];
    foreach ($pages as $products) {
        foreach ($products as $product) {
            if(! Product::find($product['id'])){
                array_push($push, [
                    'id' => $product['id'],
                    'handle' => $product['handle'],
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'price' => $product['price'],
                    'supply_price' => $product['supply_price']
                ]  );
            }
            
        }
    }
    // return sizeof($push);
    DB::table('products')->insert($push);


    // return $totalProducts;
});

Route::post('api/inventory', function (Request $request) {
    // return $request->all();

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
        'Accept'        => 'application/json',
    ];

    $response = $client->request('POST', 'https://stapog.vendhq.com/api/2.0/consignments', [
        'headers' => $headers,
        'form_params' => [
            'outlet_id' => '06bf537b-c77f-11e6-ff13-fb602832ccea',
            'name' => $request->nom,
            'type' => 'STOCKTAKE',
            'status' => 'STOCKTAKE_SCHEDULED',
            'filters' => $request->prods
        ]
    ]);
    $data = json_decode((string) $response->getBody(), true);
});

Route::get('/inventory-count', function () {
    
    $templates = App\Template::with('products')->get();
    return view('inventory.index', compact('templates'));
});



// Route::get('api/products', function (Request $request) {
//     // return $request->all();

//     $client = new Client();
//     $headers = [
//         "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
//         'Accept'        => 'application/json',
//     ];

//     $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/products?page_size=4000', [
//         'headers' => $headers
//     ]);
//     return $data = json_decode((string) $response->getBody(), true);
// });