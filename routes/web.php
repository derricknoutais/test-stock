<?php

use App\Product;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('layouts.welcome');
});

Route::resource('/product', 'ProductController');

Route::resource('/template', 'TemplateController');

Route::resource('/commande', 'CommandeController');

Route::post('/product-template', 'ProductTemplateController@addProduct');
Route::post('/product-template/delete', 'ProductTemplateController@removeProduct');

Route::post('/product-commande', 'CommandeController@addProduct');
Route::post('/template-commande', 'CommandeController@addTemplate');


Route::post('/reorderpoint-commande', 'CommandeController@addReorderPoint');



Route::get('/test','CommandeController@consignment');

Route::get('/test', 'CommandeController@addReorderPoint');

Route::post('/commande-quantité', 'CommandeController@addQuantities');













































































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

    for ($j = 1; $j <= 18; $j++) {

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