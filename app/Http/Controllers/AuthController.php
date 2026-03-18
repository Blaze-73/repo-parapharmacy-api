<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
            'telephone' => 'nullable|string|max:20',
        ], [
            'nom.required'       => 'Le nom est obligatoire.',
            'email.required'     => "L'email est obligatoire.",
            'email.unique'       => 'Cet email est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user  = User::create([
            'nom'       => $data['nom'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'telephone' => $data['telephone'] ?? null,
            'role'      => 'client',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Compte créé avec succès !',
            'data'    => [
                'user'  => $this->formatUser($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => "L'email est obligatoire.",
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect.',
                'errors'  => ['email' => ['Email ou mot de passe incorrect.']],
            ], 422);
        }

        if (! $user->actif) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été suspendu.',
            ], 403);
        }

        // Delete old tokens
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie !',
            'data'    => [
                'user'  => $this->formatUser($user),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => ['user' => $this->formatUser($request->user())],
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'        => $user->id,
            'nom'       => $user->nom,
            'email'     => $user->email,
            'telephone' => $user->telephone,
            'role'      => $user->role,
        ];
    }
}
