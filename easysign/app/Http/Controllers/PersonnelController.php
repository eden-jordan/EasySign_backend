<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;

class PersonnelController extends Controller
{
    public function store(Request $request)
    {
        $personnel = Personnel::create([
            'organisation_id'=>$request->organisation_id,
            'nom'=>$request->nom,
            'prenom'=>$request->prenom,
            'email'=>$request->email,
            'tel'=>$request->tel,
            'matricule'=>uniqid('EMP-'),
            'biometrie1'=>$request->biometrie1,
            'biometrie2'=>$request->biometrie2
        ]);

        return response()->json($personnel);
    }
}

