<?php

namespace App\Http\Controllers;
use DB;
use App\Section;
use App\Template;
use App\Sectionnable;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();
        $section = Section::create([
            'commande_id' => $request['commande'],
            'nom' => $request['section']
        ]);
        DB::table('commandables')->insert([
            'commande_id' => $request['commande'],
            'commandable_id' => $section->id,
            'commandable_type' => 'App\Section'
        ]);
        return $section;
    }

    public function addProduct(Request $request){
        // return $request['type'];
        if($request['type'] !== 'App\Template'){
            $sectionnable = Sectionnable::create([
                'section_id' => $request['section'],
                'sectionnable_id' => $request['product']['id'],
                'sectionnable_type' => $request['type'],
                'quantite' => $request['product']['quantite']
            ]);
            return $sectionnable;
        } else {
            $template = Template::where('id', $request['product'])->with('products')->first();
            $products = $template->products;
            foreach ($products as $product) {
                DB::table('sectionnables')->insert([
                    'section_id' => $request['section'],
                    'sectionnable_id' => $product->id,
                    'sectionnable_type' => 'App\Product'
                ]);
            }
            return $products;

        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function edit(Section $section)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Section $section)
    {

        $section->update([
            'nom' => $request['nom']
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function destroy(Section $section)
    {
        $section->delete();
        
    }
    public function destroyProduct(Sectionnable $product){
        $product->delete();
    }
    public function destroyArticle(Sectionnable $article){
        $article->delete();
    }
}
