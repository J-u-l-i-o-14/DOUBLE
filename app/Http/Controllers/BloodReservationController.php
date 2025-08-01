<?php

namespace App\Http\Controllers;

use App\Models\BloodType;
use App\Models\Region;
use Illuminate\Http\Request;

class BloodReservationController extends Controller
{
    public function index()
    {
        $regions = Region::all();
        $bloodTypes = BloodType::all();
        
        return view('blood-reservation', compact('regions', 'bloodTypes'));
    }

    // Cette méthode peut réutiliser la logique de recherche existante
    public function search(Request $request)
    {
        // Réutiliser la logique de SearchBloodController
        return (new SearchBloodController())->searchAjax($request);
    }
}
