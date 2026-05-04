<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use Illuminate\Http\Request;

class discipline_controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $dis=Discipline::all();
        return view('Livres.formlivre')->with(['disciplines'=>$dis]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('discipline.adddisci');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        Discipline::insert([
            'nom'=>$request->nom,
            'description'=>$request->description,
        ]);
        return redirect()->route('alllivres')->with('success','Discipline ajoutée avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
