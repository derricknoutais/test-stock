<?php

namespace App\Http\Controllers;

use App\Demande;
use Illuminate\Http\Request;
use App\Exports\DemandeExport;
use App\Imports\DemandeImport;
use Maatwebsite\Excel\Facades\Excel;

class DemandeController extends Controller
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

        $demande = Demande::create([

            'nom' => $request['fournisseur']['nom'],
            'commande_id' => $request['commande'],
            'fournisseur_id' => $request['fournisseur']['id']

        ]);
        return $demande;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Demande  $demande
     * @return \Illuminate\Http\Response
     */
    public function show(Demande $demande)
    {
        $demande->loadMissing(['sectionnables', 'sectionnables.product' , 'sectionnables.article']);
        // return $demande->loadMissing(['sectionnables', 'sectionnables.product']);
        return view('demande.show', compact('demande'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Demande  $demande
     * @return \Illuminate\Http\Response
     */
    public function edit(Demande $demande)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Demande  $demande
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Demande $demande)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Demande  $demande
     * @return \Illuminate\Http\Response
     */
    public function destroy(Demande $demande)
    {
        //
    }

    public function export(Demande $demande)
    {
        return Excel::download(new DemandeExport($demande->id), 'Demande ' . $demande->nom . '.xlsx');
    }

    public function import(Request $request){
        // return $request->file('files');
        foreach($request->file('files') as $file){
            Excel::import(new DemandeImport, $file);
        }
    }
}
