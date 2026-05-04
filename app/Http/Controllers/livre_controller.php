<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\Livre;
use Illuminate\Http\Request;
use Termwind\Components\Li;

class livre_controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //dd($request->session()->get('user'));
        if ($request->session()->get('user')) {

            $livres = Livre::all();
            $priv = $request->session()->get('priv');
            return view('Livres.alllivres')->with(['livres' => $livres])->with('user', $priv);
        } else {
            return redirect()->route('login')->with('success', 'Please log in to access the livres.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->session()->get('user')) {
            $dis = Discipline::all();
            return view('Livres.formlivre')->with(['disciplines' => $dis]);
        } else {
            return redirect()->route('login')->with('success', 'Please log in to access the livres.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        if ($request->session()->get('user')) {

            Livre::insert([
                'titre' => $request->titre,
                'auteur' => $request->auteur,
                'prix' => $request->prix,
                'nb_pages' => $request->nb_pages,
                'id_discipline' => $request->discipline,
            ]);

            return redirect()->route('alllivres')->with('success', 'Livre ajouté avec succès !');
        } else {
            return redirect()->route('login')->with('success', 'Please log in to access the livres.');
        }
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( Request $request, string $id)
    {
        //
        if ($request->session()->get('user')) {
            $livres = Livre::find($id);
            $dis = Discipline::all();
            return view('Livres.updateview')->with(['livre' => $livres, 'disciplines' => $dis]);
        } else {
            return redirect()->route('login')->with('success', 'Please log in to access the livres.');
        }
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request)
    {
        //
        if ($request->session()->get('user')) {

        $livre = Livre::find($request->id);

            $livre->update([
                'titre' => $request->titre,
                'auteur' => $request->auteur,
                'prix' => $request->prix,
                'nb_pages' => $request->nb_pages,
                'id_discipline' => $request->discipline,
            ]);
            return redirect()->route('alllivres')->with('success', 'Livre modifié avec succès !');
        } else {
            return redirect()->route('login')->with('success', 'Please log in to access the livres.');
        }
    }

    public function find(Request $request)
    {
        //
        if ($request->session()->get('user')) {
            $titre = $request->input('research');
            $priv = $request->session()->get('priv');
            $livres = Livre::where('titre', 'like', "%{$titre}%")->get();

            return view('Livres.alllivres')->with(['livres' => $livres])->with('user', $priv);
        } else {
            return redirect()->route('login')->with('success', 'Please log in to access the livres.');
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        Livre::find($id)->delete();

        return redirect()->route('alllivres')->with('success', 'Livre supprimé avec succès !');
    }
}
