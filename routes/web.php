<?php

// use DB;
use App\Product;
use App\Commande;
use App\BonCommande;
use App\Demande;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('accueil');
});

Route::resource('/product', 'ProductController');

Route::resource('/template', 'TemplateController');

Route::resource('/commande', 'CommandeController');

Route::get('/commande/{commande}/prepa-demande', function(Commande $commande){
    $commande->loadMissing(['sections', 'sections.products', 'sections.articles', 'demandes', 'demandes.sectionnables', 'demandes.sectionnables.product']);
    return view('commande.prepa-demande', compact('commande'));
});

Route::get('/commande/{commande}/demandes', function(Commande $commande){
    $commande->loadMissing('demandes', 'demandes.sectionnables');
    return view('commande.demandes', compact('commande'));
});

Route::get('/commande/{commande}/générer-bons', function(Commande $commande){
    $commande->loadMissing('demandes', 'demandes.sectionnables');
    
    // Pour Chaque Demande d'Offre de Cette Commande... 
    for ( $i = 0; $i < sizeof($commande->demandes); $i++ ) {
        
        // Pour Chaque Section de Chaque Demande
        foreach($commande->demandes[$i]->sectionnables as $sectionnable){

            //  Si le produit n'a pas encore été checké
            if(! $sectionnable->pivot->checked){

                $produitRetenu = $sectionnable;

                $moinsCher = $sectionnable->pivot->offre;

                $nom = $commande->demandes[$i]->nom;

                for ($j=0; $j < sizeof($commande->demandes); $j++) { 
                    if($j != $i){
                        foreach($commande->demandes[$j]->sectionnables as $sectionnable_comparatif){
                            if($sectionnable->sectionnable_id == $sectionnable_comparatif->sectionnable_id ){
                                if($moinsCher > $sectionnable_comparatif->pivot->offre){
                                    $produitRetenu = $sectionnable_comparatif;
                                    $moinsCher = $sectionnable_comparatif->pivot->offre;
                                    $nom = $commande->demandes[$j]->nom;
                                }
                                $sectionnable_comparatif->pivot->checked = 1;
                                DB::table('demande_sectionnable')
                                    ->where('id', $sectionnable_comparatif->pivot->id)
                                    ->update([
                                        'checked' => 1
                                    ]);
                            }
                        }
                    }
                }

                if( $bc = BonCommande::where('demande_id', $commande->demandes[$i]->id)->first() ){
                    DB::table('bon_commande_sectionnable')->insert([
                        'bon_commande_id' => $bc->id,
                        'sectionnable_id' => $produitRetenu->id,
                        'quantite' => $produitRetenu->quantite,
                        'prix_achat' => $produitRetenu->pivot->offre
                    ]);
                } 
                else
                {
                    $bc = BonCommande::create([
                        'commande_id' => $commande->id,
                        'nom' => $nom,
                        'demande_id' => $produitRetenu->pivot->demande_id
                    ]);
                    DB::table('bon_commande_sectionnable')->insert([
                        'bon_commande_id' => $bc->id,
                        'sectionnable_id' => $produitRetenu->pivot->id,
                        'quantite' => $produitRetenu->quantite,
                        'prix_achat' => $produitRetenu->pivot->offre
                    ]);
                }
                DB::table('demande_sectionnable')
                ->where('id', $sectionnable->pivot->id)
                ->update([
                    'checked' => 1
                ]);
                

            }
            

        }
        
        
    }
});

Route::get('/commande/{commande}/bon-commandes', function(Commande $commande){
    return $commande->bonsCommandes;
});


Route::resource('/section', 'SectionController');

Route::resource('/demande', 'DemandeController');
Route::post('/demande-sectionnable', function(Request $request){
    // return $request->all();
    foreach ($request['demandes'] as $demande ) {
        foreach ($request['products'] as $product) {
            DB::table('demande_sectionnable')->insert([
                'demande_id' => $demande['id'],
                'sectionnable_id' => $product['pivot']['id'],
                'offre' => 0
            ]); 
        }
    }
});

Route::put('demande/{demande}/update-product', function (Demande $demande, Request $request) {

    DB::table('demande_sectionnable')->where('id', $request['pivot']['id'])->update([
        'offre' => $request['pivot']['offre']
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