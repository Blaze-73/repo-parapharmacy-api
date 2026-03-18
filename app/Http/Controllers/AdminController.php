<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function checkAdmin(Request $request): ?JsonResponse
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        return null;
    }

    public function dashboard(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        return response()->json(['success' => true, 'data' => [
            'total_produits'     => Produit::count(),
            'total_commandes'    => Commande::count(),
            'total_clients'      => User::where('role', 'client')->count(),
            'chiffre_affaires'   => Commande::where('statut', '!=', 'annulee')->sum('total'),
            'commandes_recentes' => Commande::with('user')->latest()->limit(10)->get(),
            'produits_stock_bas' => Produit::where('stock', '<=', 5)->where('actif', true)->get(['id', 'nom', 'stock']),
        ]]);
    }

    // ── Produits ──────────────────────────────────────────────────────────────
    public function produits(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        $produits = Produit::with('categorie')
            ->when($request->filled('q'), fn($q) => $q->where('nom', 'like', '%' . $request->q . '%'))
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $produits->items(),
            'meta'    => ['total' => $produits->total()],
        ]);
    }

    public function storeProduit(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        $data = $request->validate([
            'nom'          => 'required|string|max:255',
            'description'  => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
            'marque'       => 'nullable|string',
            'prix'         => 'required|numeric|min:0',
            'prix_promo'   => 'nullable|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'en_vedette'   => 'nullable|boolean',
            'actif'        => 'nullable|boolean',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('produits', 'public');
        }

        $produit = Produit::create([
            'nom'          => $data['nom'],
            'slug'         => Str::slug($data['nom']) . '-' . Str::random(4),
            'description'  => $data['description'] ?? null,
            'categorie_id' => $data['categorie_id'],
            'marque'       => $data['marque'] ?? null,
            'prix'         => $data['prix'],
            'prix_promo'   => $data['prix_promo'] ?? null,
            'stock'        => $data['stock'],
            'en_vedette'   => $request->boolean('en_vedette'),
            'actif'        => $request->has('actif') ? $request->boolean('actif') : true,
            'image'        => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produit créé avec succès !',
            'data'    => $produit->load('categorie'),
        ], 201);
    }

    public function updateProduit(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        $produit = Produit::findOrFail($id);

        $data = $request->validate([
            'nom'          => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'categorie_id' => 'sometimes|exists:categories,id',
            'marque'       => 'nullable|string',
            'prix'         => 'sometimes|numeric|min:0',
            'prix_promo'   => 'nullable|numeric|min:0',
            'stock'        => 'sometimes|integer|min:0',
            'en_vedette'   => 'nullable|boolean',
            'actif'        => 'nullable|boolean',
            'image'        => 'nullable|image|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($produit->image) {
                \Storage::disk('public')->delete($produit->image);
            }
            $data['image'] = $request->file('image')->store('produits', 'public');
        }

        $produit->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Produit mis à jour.',
            'data'    => $produit->fresh('categorie'),
        ]);
    }

    public function deleteProduit(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        $produit = Produit::findOrFail($id);

        // Delete image file if exists
        if ($produit->image) {
            \Storage::disk('public')->delete($produit->image);
        }

        $produit->delete();

        return response()->json(['success' => true, 'message' => 'Produit supprimé.']);
    }

    // ── Commandes ─────────────────────────────────────────────────────────────
    public function commandes(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        $commandes = Commande::with('user')
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $commandes->items(),
            'meta'    => ['total' => $commandes->total()],
        ]);
    }

    public function updateStatutCommande(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        $request->validate([
            'statut' => 'required|in:en_attente,confirmee,expediee,livree,annulee',
        ]);

        $commande = Commande::findOrFail($id);
        $commande->update(['statut' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour.',
            'data'    => $commande,
        ]);
    }

    // ── Utilisateurs ──────────────────────────────────────────────────────────
    public function utilisateurs(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        $users = User::where('role', 'client')
            ->withCount('commandes')
            ->when($request->filled('q'), fn($q) => $q->where('nom', 'like', '%' . $request->q . '%')
                ->orWhere('email', 'like', '%' . $request->q . '%'))
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $users->items()]);
    }

    // ── Categories ────────────────────────────────────────────────────────────
    public function categories(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        return response()->json([
            'success' => true,
            'data'    => Categorie::withCount('produits')->get(),
        ]);
    }

    public function storeCategorie(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin($request)) return $err;

        $data = $request->validate([
            'nom'   => 'required|string',
            'icone' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['nom']);
        $cat = Categorie::create($data);

        return response()->json(['success' => true, 'data' => $cat], 201);
    }
}
