<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'role' => $request->role ?? 'CITIZEN',
        ]);

        // Utilisation explicite du guard 'api' pour générer le token JWT
        $token = auth('api')->login($user);

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Identifiants incorrects'], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => auth('api')->user() 
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        // = 422 = fcm_token est vide
        $request->validate(['fcm_token' => 'required']);

        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé via le Token'], 401);
        }

        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['message' => 'Token mis à jour pour ' . $user->nom]);
    }
}