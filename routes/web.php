<?php
ini_set('max_execution_time', 180);
// use DB;
use App\Product;
use App\Consignment;
use App\Sales;
use App\Commande;
use App\BonCommande;
use App\Demande;
use App\Fournisseur;
use GuzzleHttp\Client;
use Illuminate\Http\Request;


// Welcome
Route::get('/', function () {
    return view('accueil');
});


// Products
Route::resource('/product', 'ProductController');



// Templates
Route::resource('/template', 'TemplateController');

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

    Route::get('/section-product/delete/{article}/{section}', function($article, $section){
        $var = DB::table('sectionnables')->where(['section_id' => $section, 'sectionnable_id' => $article])->delete();
        return $var;
    });



// Prépa-Demande
Route::get('/commande/{commande}/prepa-demande', 'DemandeController@showPrepaDemande');
Route::post('/demande-sectionnable', 'DemandeController@addSectionnable');
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
Route::get('/commande/{commande}/bons-commandes', 'BonCommandeController@index');
Route::get('/commande/{commande}/bons-commandes/{bc}', 'BonCommandeController@show');
Route::get('/commande/{commande}/générer-bons', 'BonCommandeController@générerBonsCommandes');
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
Route::get('/test','CommandeController@consignment');



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


Route::get('/subzero/{product}', function ($product) {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
        'Accept'        => 'application/json',
    ];
    $response = $client->request('GET', 'http://subzero.azimuts.ga/api/sub/' . $product);
    return $data = json_decode((string) $response->getBody(), true);
});


// VEND API
Route::get('/vend/update-quantities', function(){
    $client = new Client();
    $headers = [
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
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
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
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
            if (!Product::where('id', $product['id'])->first()) {

                $prod = Product::create([
                    'id' => $product['id'],
                    'handle' => $product['handle'],
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'price' => $product['price'],
                    'supply_price' => $product['supply_price']
                ]);

                if( isset($product['inventory']) && isset($product['inventory'][0]['count'])){
                    Product::find($product['id'])->update([
                        'quantity' => ( (int) $product['inventory'][0]['count'] )
                    ]) ;
                }
            }
        }
    }


    // return $totalProducts;
});

Route::get('/api/sales', function () {

    $client = new Client();
    $headers = [
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
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
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
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
        "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
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

Route::post('api/inventory', 'TemplateController@createVend');
Route::get('/inventory-count', 'TemplateController@inventory');

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
