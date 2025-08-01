<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected function redirectTo()
    {
        // Tous les nouveaux inscrits sont des clients, donc on les redirige vers la page d'accueil
        if (session()->has('search_params')) {
            $params = session('search_params');
            return route('home') . '?' . http_build_query($params);
        }
        
        return route('home');
    }

    protected function register(Request $request)
    {
        // Sauvegarder les paramètres de recherche dans la session
        if ($request->has('redirect_params')) {
            session(['search_params' => json_decode($request->redirect_params, true)]);
        }

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectTo());
    }

    // ... reste du code RegisterController
}
