<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Login_Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('loginView');
    }

    public function connexion(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = DB::table('users')->where('email','=' , $email)->where('password','=', $password)->get();
    //print_r(count($user));
        if (count($user)) {

            $request->session()->put('user', $request->email);
            $request->session()->put('priv', $user[0]->privilege);
    //dd($request->session()->get('user'));
            return redirect()->route('alllivres');
            //->with(['user' => $request->session()->get('user')])->with('priv', $request->session()->get('priv'))->with('success', 'Login successful. Welcome, ' . $user[0]->name . ' !');
        } else {
            return redirect()->back()->with(['success' => 'Invalid email or password']);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('RegisterView');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user');
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $nom = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $confirm_password = $request->input('password_confirmation');

        if ($password !== $confirm_password) {
            return redirect()->back()->with(['success' => 'Passwords do not match']);
        }
        if (User::where('email', $email)->exists()) {
            return redirect()->back()->with(['success' => 'Email already exists']);
        } else {
            User::insert([
                'name' => $nom,
                'email' => $email,
                'password' => $password,
            ]);

            return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
        }
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
