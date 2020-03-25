<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Fournisseur;

class ProductController extends Controller
{
    public function index(){
        $products = Product::with('fournisseurs')->get();
        $fournisseurs = Fournisseur::all();
        return view('products.index',compact('products', 'fournisseurs'));
    }



}
