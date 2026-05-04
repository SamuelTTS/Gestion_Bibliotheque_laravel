<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\BiblioRessource;
use App\Models\Livre;

class ApiBiblioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return ['Livres'=>BiblioRessource::collection(Livre::all())];
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validate = $request->validate([
            'titre' => 'string|required',
            'auteur' => 'string|required',
            'prix' => 'required|numeric',
            'nb_pages' => 'required|integer',
            'id_discipline' => 'required|numeric'
        ]);
        Livre::create($validate);
        echo "test";
        return response()->json(['insert'=>200]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $livre = Livre::find($id);
        return ['livre'=>BiblioRessource::make($livre)];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $livre = Livre::find($id);
        $livre->update($request->all());
        return response()->json(['update'=>200]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $livre = Livre::find($id);
        $livre->delete();
        return response()->json(['delete'=>200]);
    }
}
