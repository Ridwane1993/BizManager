<?php

namespace App\Http\Controllers;

class ProfileController extends Controller
{
    /**
     * Afficher le profil et les passkeys de l'utilisateur connecté.
     */
    public function edit()
    {
        $user = auth()->user();

        $passkeys = $user->passkeys()
            ->latest('created_at')
            ->get();

        return view('profile.edit', compact('passkeys'));
    }
}
