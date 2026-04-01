<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('admin_logged')) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $user     = $request->input('username');
        $password = $request->input('password');

        if (
            $user === config('services.admin.user') &&
            $password === config('services.admin.password')
        ) {
            session(['admin_logged' => true]);
            return redirect()->route('home');
        }

        return back()->withErrors(['credentials' => 'Usuario o contraseña incorrectos.']);
    }

    public function logout()
    {
        session()->forget('admin_logged');
        return redirect()->route('login');
    }

    public function showHome()
    {
        return view('home');
    }
}