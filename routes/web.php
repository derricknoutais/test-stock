<?php
ini_set('max_execution_time', 180);
// use DB;
use App\Product;
use App\Consignment;
use App\Sales;
use App\Commande;
use App\BonCommande;
use App\Demande;
use App\Section;
use App\Facture;
use App\Fournisseur;
use App\Sectionnable;
use GuzzleHttp\Client;
use Illuminate\Http\Request;


// Welcome
Route::get('/', function () {
    return view('accueil');
});


Route::get('/test', function(){
    return Product::where('id', '06bf537b-c771-11e6-ff13-fc4651e2a841')->with('handle')->first();
});

// Products
Route::resource('/product', 'ProductController');


// Handles
Route::resource('/handle', 'HandleController');


// Templates
Route::resource('/template', 'TemplateController');
Route::get('/template/type/{type}', 'TemplateController@type');



Route::post('/product-template', 'ProductTemplateController@addProduct');
Route::post('/product-template/delete', 'ProductTemplateController@removeProduct');
Route::put('product-template', function ( Request $request) {
    foreach ($request->all() as $prodTemp) {
        DB::table('product_template')->where(['template_id' => $prodTemp['template_id'] , 'product_id' => $prodTemp['product_id']])->update([
            'quantite' => $prodTemp['quantite']
        ]);
    }
    return $request->all();
});


// Commandes
Route::resource('/commande', 'CommandeController');
Route::post('/product-commande', 'CommandeController@addProduct');
Route::post('/template-commande', 'CommandeController@addTemplate');
Route::get('/dernières-commandes/{sectionnable_type}/{selected_article}', function($sectionnable_type, $selected_article){
    // return $selected_article;
    if($sectionnable_type === 'Product'){
        $commande_en_cours = Commande::where('state', '<>', 'Terminé')->pluck('id');
        $sections_en_cours = Section::whereIn('commande_id', $commande_en_cours)->pluck('id');
        $sectionnables = Sectionnable::where('sectionnable_id', $selected_article)->whereIn('section_id', $sections_en_cours)->pluck('id');
        $bc_sectionnable_pluck =  DB::table('bon_commande_sectionnable')->whereIn('sectionnable_id', $sectionnables)->pluck('bon_commande_id');
        $bc_sectionnable =  DB::table('bon_commande_sectionnable')->whereIn('sectionnable_id', $sectionnables)->get();
        $bon_commande = BonCommande::whereIn('id', $bc_sectionnable_pluck)->get();
        foreach ($bon_commande as $bc ) {
            $bc['commande'] = Commande::find($bc->commande_id);
            foreach($bc_sectionnable as $sec){
                if($sec->bon_commande_id === $bc->id){
                    $bc['sectionnable'] = $sec;
                }
            }
        }

        return  $bon_commande;

    }
    // return $selected_article;
});



/**
    ************* SECTION ***************
    * Chaque commande a plusieurs sections
*/
Route::resource('/section', 'SectionController');
Route::post('/product-section', 'SectionController@addProduct');

// Sectionnables

    // Supprimer des sectionnables
    Route::delete('/sectionnable/{product}', 'SectionController@destroyProduct');
    Route::delete('/sectionnable/{article}', 'SectionController@destroyArticle');
    Route::patch('/sectionnable', 'SectionController@patchSectionnable');
    Route::get('/section-product/delete/{article}/{section}', function($article, $section){
        $var = DB::table('sectionnables')->where(['section_id' => $section, 'sectionnable_id' => $article])->delete();
        return $var;
    });

    Route::get('/prendre-offre-de/{commande_offre}/pour/{this_commande}', function(Commande $commande_offre, Commande $this_commande){

        $this_commande->loadMissing('sections', 'sections.sectionnables', 'sections.sectionnables.demandes');
        $this_sectionnables = Sectionnable::whereIn('section_id', Section::where('commande_id', $this_commande->id)->pluck('id') )->with('demandes')->get();
        $offre_sectionnables = Sectionnable::whereIn('section_id', Section::where('commande_id', $commande_offre->id)->pluck('id') )->with('demandes')->get();
        $array = [];
        foreach ($this_sectionnables as $sectionnable ) {
            foreach($offre_sectionnables as $sectionnable_correspondant){
                if($sectionnable_correspondant['sectionnable_id'] === $sectionnable->sectionnable_id){

                    foreach ($sectionnable_correspondant->demandes as $demande_trouvee) {

                        foreach($sectionnable->demandes as $demande_cherchee){
                            if($demande_trouvee->fournisseur_id === $demande_cherchee->fournisseur_id){
                                array_push($array, $demande_cherchee->id);
                                DB::table('demande_sectionnable')->where('id', $demande_cherchee->pivot->id)->update([
                                    'quantite_offerte' => $demande_cherchee->pivot->quantite,
                                    'offre' => $demande_trouvee->pivot->offre,
                                    'differente_offre' => $demande_trouvee->pivot->differente_offre,
                                    'reference_differente_offre' => $demande_trouvee->pivot->differente_offre
                                ]);
                            }
                        }

                    }

                }
            }
        }
        return $array;
    });

// Prépa-Demande
Route::get('/commande/{commande}/prepa-demande', 'DemandeController@showPrepaDemande');
Route::post('/demande-sectionnable', 'DemandeController@addSectionnable');
Route::put('/demande-sectionnable', 'DemandeController@updateSectionnable');
Route::patch('/demande-sectionnable', 'DemandeController@patchSectionnable');
Route::put('/demande-sectionnable', 'DemandeController@updateSectionnable');
Route::patch('/demande-sectionnable-traduction', 'DemandeController@updateTraduction');
Route::get('/commande/{commande}/dispatch-produits-dans-demandes', 'DemandeController@dispatchSectionnables');



// Demandes
Route::resource('/demande', 'DemandeController');
Route::get('/commande/{commande}/demandes', 'DemandeController@index');
Route::delete('demande-sectionnable/{id}', 'DemandeController@destroySectionnable');
Route::post('/import', 'DemandeController@import');
Route::put('demande/{demande}/update-product', 'DemandeController@updateProduct');
Route::get('demande/export/{demande}', 'DemandeController@export');


// Conflits
Route::get('/commande/{commande}/conflits', 'BonCommandeController@showConflit');
Route::post('/commande/{commande}/résoudre-conflit', function(Commande $commande, Request $request){

        // Fonction Résoudre Conflit

        /*
            Si le bon de commande du fournisseur pour le produit choisi existe,
            Nous allons simplement inséré le produit en question à l'intérieur
        */
        if( $bc = BonCommande::where('demande_id' , $request['element']['demande_id'] )->first() )
        {
            DB::table('bon_commande_sectionnable')->insert([
                'bon_commande_id' => $bc->id,
                'sectionnable_id' => $request['element']['sectionnable_id'],
                'quantite' => $request['element']['quantite_offerte'],
                'prix_achat' => $request['element']['offre']
            ]);
        /*
            Dans le cas contraire
        */

        } else {
            $bc = BonCommande::create([
                'commande_id' => $commande->id,
                'nom' => 'Bon Commande ' . $request['element']['demande']['nom'] ,
                'demande_id' => $request['element']['demande_id']
            ]);
            DB::table('bon_commande_sectionnable')->insert([
                'bon_commande_id' => $bc->id,
                'sectionnable_id' => $request['element']['sectionnable_id'],
                'quantite' => $request['element']['quantite_offerte'],
                'prix_achat' => $request['element']['offre']
            ]);
        }

        DB::table('demande_sectionnable')
        ->where('id', $request['element']['id'])
        ->update([
            'checked' => 1
        ]);

        DB::table('sectionnables')
        ->where('id', $request['element']['sectionnable_id'])
        ->update([
            'conflit' => 0
        ]);
});
Route::get('/erase-conflits', function(){
    $sectionnables = App\Sectionnable::all();
    foreach($sectionnables as $sectionnable){
        $sectionnable->update([
            'conflit' => 0
        ]);
    }
});


// Bons de Commandes
Route::patch('/transfer-sectionnable-to-bon-commandes', function(Request $request){
    if(
        $bc = BonCommande::where([
        'demande_id' => $request['dem']['pivot']['demande_id']
        ])->first()
    ){
        DB::table('bon_commande_sectionnable')->where('id', $request['sectionnable']['bon_commande'][0]['pivot']['id'])->update([
            'bon_commande_id' => $bc->id,
            'quantite' => $request['dem']['pivot']['quantite_offerte'],
            'prix_achat' => $request['dem']['pivot']['offre']
        ]);
    } else {
        $bc = BonCommande::create([
            'nom' => $request['dem']['nom'],
            'fournisseur_id' => $request['dem']['fournisseur_id'],
            'commande_id' => $request['dem']['commande_id']
        ]);
        DB::table('bon_commande_sectionnable')->where('id', $request['sectionnable']['bon_commande'][0]['pivot']['sectionnable_id'])->first()->update([
            'bon_commande_id' => $bc->id
        ]);
    }
});
Route::get('/commande/{commande}/bons-commandes', 'BonCommandeController@index');
Route::get('/commande/{commande}/bons-commandes/{bc}', 'BonCommandeController@show');
Route::get('/commande/{commande}/générer-bons', 'BonCommandeController@générerBons');
Route::get('/bons-commandes/export/{bon_commande}', 'BonCommandeController@export');
Route::get('/commande/{commande}/export-all-bons-commandes', 'BonCommandeController@exportall');

Route::post('/bon-commande/sectionnable', 'BonCommandeController@storeSectionnable');

Route::put('/bon-commande/{sectionnable}', 'BonCommandeController@updateSectionnable');
Route::put('/bon-commande/sectionnables', 'BonCommandeController@updateAllSectionnable');
Route::delete('/bon-commande/sectionnable/{sectionnable}', 'BonCommandeController@destroySectionnable');
Route::get('/bon-commande/{bc}/create-invoice', 'BonCommandeController@createInvoice');



// Factures

Route::get('/commande/{commande}/factures', 'FactureController@index');
Route::get('facture/{facture}', 'FactureController@show');
Route::put('/facture/{sectionnable}', 'FactureController@updateSectionnable');
Route::delete('/facture/sectionnable/{sectionnable}', 'FactureController@destroySectionnable');
Route::post('/facture/sectionnable', 'FactureController@storeSectionnable');

// Fournisseurs
Route::resource('/fournisseur', 'FournisseurController');
Route::post('product-fournisseur', function(Request $request){

    // $product = Product::where('id', $request['product']['id'])->first();
    $found = DB::table('product_fournisseur')->where( ['product_id' => $request['product']['id'] ])->delete();
    foreach($request['product']['fournisseurs'] as $fournisseur){
        DB::table('product_fournisseur')->insert([
            'fournisseur_id' => $fournisseur['id'],
            'product_id' => $request['product']['id']
        ]);
    }

        // foreach($products as $product){
        //     $found = DB::table('product_fournisseur')->where( ['product_id' => $product->id ])->delete();

        //     foreach($request['product']['fournisseurs'] as $fournisseur){
        //         DB::table('product_fournisseur')->insert([
        //             'fournisseur_id' => $fournisseur['id'],
        //             'product_id' => $product->id
        //         ]);
        //     }
        // }
});


Route::get('/remettre-a-zero', function(){
    DB::table('demande_sectionnable')->whereIn('demande_id', [4,5,6,7,8])->update([
        'checked' => 0
    ]);
    $bcs = DB::table('bon_commandes')->whereIn('demande_id', [4,5,6,7,8])->pluck('id');
    DB::table('bon_commande_sectionnable')->whereIn('bon_commande_id', $bcs)->delete();
    DB::table('bon_commandes')->whereIn('demande_id', [4,5,6,7,8])->delete();
});

Route::get('/kd', function(){
    return App\Sectionnable::all();
});










Route::get('/reorderpoint-commande', 'CommandeController@addReorderPoint');
// Route::get('/test','CommandeController@consignment');



Route::post('/commande-quantité', 'CommandeController@addQuantities');


Route::get('/t', function(){
    $produits = App\Product::where('handle', 'PlateauDembrayageAisin')->get();
    return view('work', compact('produits'));
});



// /section-product/delete/' + article.id + '/' + section.id






Route::put('article-update', function( Request $request){
    // return $request->all();
    $art = DB::table('sectionnables')->where('id', $request['article']['pivot']['id'])->update([
            'quantite' => $request['article']['pivot']['quantite']
        ]);
});




Route::get('/quantite-vendue/{product}', function($product){

    return Sales::where('product_id', $product)->first();
});



Route::get('/consignment/{product}', function($product){

    return $cons = DB::table('consignment_product')->where('product_id', $product)->sum('count');
});


Route::get('/subzero/{product}/{apres?}/{avant?}', function ($product, $apres = null, $avant = null) {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];
    $response = $client->request('GET', 'http://subzero.azimuts.ga/api/sub/' . $product . ($apres ?  '/' . $apres : null ) . ($avant ?  '/' . $avant : null));
    return $data = json_decode((string) $response->getBody(), true);
});


// VEND API

Route::post('/creer-bl', function(Request $request){

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];
    // Créer un nouveau Consignment
    $response = $client->request('POST', 'https://stapog.vendhq.com/api/consignment', [
        'headers' => $headers,
        'body' => json_encode([
            'outlet_id' => '06bf537b-c77f-11e6-ff13-fb602832ccea',
            'name' => $request->nom . ' ' . $request['commande']['name'],
            'type' => 'SUPPLIER',
            'status' => 'OPEN'
        ])
    ]);
    $data = json_decode((string) $response->getBody(), true);

    // Créer un Produit dans le consignment
    foreach ($request['sectionnables'] as $sect) {
        $response2 = $client->request('POST', 'https://stapog.vendhq.com/api/consignment_product', [
            'headers' => $headers,
            'body' => json_encode([
                'consignment_id' => $data['id'],
                'product_id' => $sect['sectionnable_id'],
                'count' => $sect['pivot']['quantite'],
                'cost' => $sect['pivot']['prix_achat']
            ])
        ]);
    }

    Facture::find($request['id'])->update([
        'bon_livraison_id' => $data['id']
    ]);
});
Route::get('/api/vend/commande/{commande_id}/reorderpoint/{reorderpoint_id}/', function($commande_id, $reorderpoint_id){
    $client = new Client();
    $headers = [
        "Authorization" => "Bearer "  . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];
    $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment_product?consignment_id=' . $reorderpoint_id  . '&page_size=200' , [
        'headers' => $headers
    ]);


    $data = json_decode((string) $response->getBody(), true);

    $totaux = array();
    $totaux['products'] = sizeof($data['consignment_products']);
        if( ! $section = Section::where( ['nom' => 'Reorder Point', 'commande_id' => $commande_id ] )->first() )
        {
            $section = Section::create([
                'nom' => 'Reorder Point',
                'commande_id' => $commande_id
            ]);
        }
    $totaux['inserted'] = 0;
    $totaux['duplicatas'] = array();
    // Pour chaque produit de la commande
    $sections = Section::where('commande_id', $commande_id)->with('sectionnables')->get();
    foreach( $data['consignment_products'] as $product )
    {
        $found = false;
        // Je dois avoir toutes les sections
        foreach($sections as $section){
            foreach($section->sectionnables as $sectionnable){
                if($sectionnable->sectionnable_id === $product['product_id']){
                    $found = true;
                }
            }
        }

        // Vérifier que ca n'existe dans aucune section

        if( !$found ){
            DB::table('sectionnables')->insert([
                'section_id' => $section->id,
                'sectionnable_id' => $product['product_id'],
                'sectionnable_type' => 'App\\Product',
                'quantite' => $product['count']
            ]);
            $totaux['inserted'] += 1;
        } else {
            array_push($totaux['duplicatas'], $product);
        }
    }
    return $totaux;
});

Route::get('/api/stock/{product}', function($product){
    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];
    $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/products/' . $product . '/inventory'  , [
        'headers' => $headers
    ]);

    $data = json_decode((string) $response->getBody(), true);
    return $data['data'][0]['inventory_level'];

});
Route::get('/vend/update-quantities', function(){
    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];

    $pages = array();

    for ($j = 1; $j <= 18; $j++) {

        $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
        $data = json_decode((string) $response->getBody(), true);
        array_push($pages, $data['products']);

    }
    foreach ($pages as $products) {
        foreach ($products as $product) {
            if ( Product::where('id', $product['id'])->first()) {
                if( isset($product['inventory']) && isset($product['inventory'][0]['count'])){
                    Product::find($product['id'])->update([
                        'quantity' => ( (int) $product['inventory'][0]['count'] )
                    ]) ;
                }
            }
        }
    }
});
Route::get('/api/produits', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];

    $pages = array();

    for ($j = 1; $j <= 18; $j++) {

        $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
        $data = json_decode((string) $response->getBody(), true);
        array_push($pages, $data['products']);


    }
    foreach ($pages as $products) {
        foreach ($products as $product) {
            if($product['handle'] == 'filtreahuilebosch' || $product['handle'] == 'bougiesallumagetoyota'){
                Product::find($product['id'])->update([
                    'variant_option_one_name' => $product['variant_option_one_name'],
                    'variant_option_one_value' => $product['variant_option_one_value'],
                    'variant_option_two_name' => $product['variant_option_two_name'],
                    'variant_option_two_value' => $product['variant_option_two_value'],
                    'variant_option_three_name' => $product['variant_option_three_name'],
                    'variant_option_three_value' => $product['variant_option_three_value']
                ]) ;
            }
            // if (! Product::where('id', $product['id'])->first()) {

            //     $prod = Product::create([
            //         'id' => $product['id'],
            //         'handle' => $product['handle'],
            //         'name' => $product['name'],
            //         'sku' => $product['sku'],
            //         'price' => $product['price'],
            //         'supply_price' => $product['supply_price'],
            //         'variant_option_one_name' => $product['variant_option_one_name'],
            //         'variant_option_one_value' => $product['variant_option_one_value'],
            //         'variant_option_two_name' => $product['variant_option_two_name'],
            //         'variant_option_two_value' => $product['variant_option_two_value'],
            //         'variant_option_three_name' => $product['variant_option_three_name'],
            //         'variant_option_three_value' => $product['variant_option_three_value']
            //     ]);

            //     if( isset($product['inventory']) && isset($product['inventory'][0]['count'])){
            //         Product::find($product['id'])->update([
            //             'quantity' => ( (int) $product['inventory'][0]['count'] )
            //         ]) ;
            //     }
            // } else {
            //     Product::find($product['id'])->update([
            //         'variant_option_one_name' => $product['variant_option_one_name'],
            //         'variant_option_one_value' => $product['variant_option_one_value'],
            //         'variant_option_two_name' => $product['variant_option_two_name'],
            //         'variant_option_two_value' => $product['variant_option_two_value'],
            //         'variant_option_three_name' => $product['variant_option_three_name'],
            //         'variant_option_three_value' => $product['variant_option_three_value']
            //     ]) ;
            // }
        }
    }


    // return $totalProducts;
});

Route::get('/api/handle', function(){

});

Route::get('/api/sales', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];

    $pages = array();
    for ($j = 1; $j <= 40; $j++) {
        $response = $client->request('GET', 'https://stapog.vendhq.com/api/register_sales?since=2019-04-01T00:00:01&status=CLOSED&status=ONACCOUNT&status=LAYBY&status=ONACCOUNT_CLOSED&status=LAYBY_CLOSED&status=LAYBY&status=AWAITING_DISPATCH&status=AWAITING_PICKUP&status=DISPATCHED_CLOSED&status=PICKED_UP_CLOSED&page=' . $j, ['headers' => $headers]);
        $data = json_decode((string) $response->getBody(), true);
        array_push($pages, $data['register_sales']);
    }
    // return $pages;
    $start_trim1 = new DateTime('2019-04-01');
    $end_trim1 = new DateTime('2019-06-31');
    $start_trim2 = new DateTime('2019-07-01');
    $end_trim2 = new DateTime('2019-09-31');
    $start_trim3 = new DateTime('2019-10-01');
    $end_trim3 = new DateTime('2019-12-31');
    $start_trim4 = new DateTime('2020-01-01');
    $end_trim4 = new DateTime('2020-03-31');
    $start_trim5 = new DateTime('2020-04-01');
    foreach($pages as $page){
        for($i =0 ; $i < sizeof($page); $i++){
            $sale_date = new DateTime($page[$i]['sale_date']);
            if($sale_date >= $start_trim1 && $page[$i]['status'] !== 'SAVED'){
                foreach($page[$i]['register_sale_products'] as $sale_product){

                    if($sale = Sales::where('product_id', $sale_product['product_id'])->first()){

                        $sale->increment('quantite_vendue', $sale_product['quantity']);
                        //
                        if( $sale_date >= $start_trim1 && $sale_date <= $end_trim1 ){
                            $sale->increment('Trim1',  $sale_product['quantity']);
                        } else if( $sale_date >= $start_trim2 && $sale_date <= $end_trim2 ){
                            $sale->increment('Trim2',  $sale_product['quantity']);
                        } else if( $sale_date >= $start_trim3 && $sale_date <= $end_trim3 ){
                            $sale->increment('Trim3',  $sale_product['quantity']);
                        } else if( $sale_date >= $start_trim4 && $sale_date <= $end_trim4 ){
                            $sale->increment('Trim4',  $sale_product['quantity']);
                        } else if( $sale_date >= $start_trim5 ){
                            $sale->increment('Trim5',  $sale_product['quantity']);
                        }


                    } else {

                        $sale = Sales::create([
                            'product_id' => $sale_product['product_id'],
                            'quantite_vendue' => $sale_product['quantity']
                        ]);

                        if( $sale_date >= $start_trim1 && $sale_date <= $end_trim1 ){
                            $sale->increment('Trim1',  $sale_product['quantity']);
                        } else if( $sale_date >= $start_trim2 && $sale_date <= $end_trim2 ){
                            $sale->increment('Trim2',  $sale_product['quantity']);
                        } else if( $sale_date >= $start_trim3 && $sale_date <= $end_trim3 ){
                            $sale->increment('Trim3',  $sale_product['quantity']);
                        } else if( $sale_date >= $start_trim4 && $sale_date <= $end_trim4 ){
                            $sale->increment('Trim4',  $sale_product['quantity']);
                        } else if( $sale_date >= $start_trim5 ){
                            $sale->increment('Trim5',  $sale_product['quantity']);
                        }
                    }
                }
            }

        }
    }

});

Route::get('/api/stocktake', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];

    $pages = array();

    for ($j = 1; $j <= 1; $j++) {
        $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment?since=2020-01-01T00:00:01&page_size=200&page=' . $j, ['headers' => $headers]);
        // $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment_product?product_id=0af7b240-ab71-11e7-eddc-5e6cb15d832a', ['headers' => $headers]);
         $data = json_decode((string) $response->getBody(), true);
    }

    foreach($data['consignments'] as $cons){
        if( $cons['type'] == 'SUPPLIER' ){
            Consignment::create([
                'id' => $cons['id'],
                'name' => $cons['name'],
                'due_at' => $cons['due_at'],
                'status' => $cons['status'],
                'type' => $cons['type']
            ]);
        }
    }
    return 'Ok';
});

Route::get('/api/stocktake/products', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
        'Accept'        => 'application/json',
    ];

    $pages = array();
    $stocktakes = Consignment::where('status', '<>', 'RECEIVED')->get();

    for ($j = 0; $j < sizeof($stocktakes); $j++) {
        $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment_product?consignment_id=' . $stocktakes[$j]['id'], ['headers' => $headers]);
        // $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment_product?product_id=0af7b240-ab71-11e7-eddc-5e6cb15d832a', ['headers' => $headers]);
        $data = json_decode((string) $response->getBody(), true);
        array_push($pages, $data['consignment_products']);
    }

    foreach($pages as $page){
        foreach($page as $cons){

            DB::table('consignment_product')->insert([
                'consignment_id' => $cons['consignment_id'],
                'product_id' => $cons['product_id'],
                'count' => (int) $cons['count'],
                'cost' => (double) $cons['cost']
            ]);
        }

    }
    return 'Ok';
});


Route::get('/api/stock', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
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
        "Authorization" => "Bearer " . env('VEND_TOKEN'),
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

Route::post('api/inventory', 'TemplateController@createVend');
Route::get('/inventory-count', 'TemplateController@inventory');

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
