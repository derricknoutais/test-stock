<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Fournisseur;
use App\Handle;

class ProductController extends Controller
{
    public function index(Request $request){
        if(isset($request->handle)){
            $products = Product::where('handle_id', $request->handle )->with('fournisseurs')->get();
        } else {
            $products = Product::where('handle_id', 1)->with('fournisseurs')->get();
        }
        $handles = Handle::all();
        $fournisseurs = Fournisseur::orderBy('nom')->get();

        return view('products.index',compact('products', 'fournisseurs', 'handles'));
    }

}
