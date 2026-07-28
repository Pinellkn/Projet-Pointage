<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Connexion (employé ou DG) — retourne un token Sanctum.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        $token = $user->createToken('pointage')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Déconnexion — révoque le token courant.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Session courante (équivalent getCurrentSession du front).
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * Indique si un compte DG existe déjà (utilisé par la page /dg).
     */
    public function checkDgExists()
    {
        return response()->json([
            'exists' => User::where('role', 'dg')->exists(),
        ]);
    }

    /**
     * Crée le tout premier compte DG. Refuse si un DG existe déjà.
     * Route non authentifiée, volontairement — c'est l'équivalent du
     * "bootstrapDG" côté Supabase.
     */
    public function bootstrapDg(Request $request)
    {
        if (User::where('role', 'dg')->exists()) {
            throw ValidationException::withMessages([
                'email' => ["Un DG existe déjà. Contactez l'administrateur."],
            ]);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'fullName' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $user = User::create([
            'name' => $data['fullName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'dg',
            'position' => 'Directeur Général',
        ]);

        $token = $user->createToken('pointage')->plainTextToken;

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'fullName' => $user->name,
            'position' => $user->position,
            'department' => $user->department,
            'isDg' => $user->isDg(),
            'isEmployee' => $user->isEmployee(),
            'createdAt' => $user->created_at,
        ];
    }
}
