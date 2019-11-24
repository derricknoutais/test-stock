<?php

namespace App\Http\Controllers;

use App\Product;
use App\Template;
use DB;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(){
        $templates = Template::all();

        return view('template.index', compact('templates'));
    }

    public function show(Template $template){
        
        $template->loadMissing('products');
        $products = Product::all();
        return view('template.show', compact('template', 'products'));

    }

    public function store(Request $request){
        $template = Template::create([
            'name' => $request->name
        ]);
        if ($template)
            return redirect()->back();
    }


}
