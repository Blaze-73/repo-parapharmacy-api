<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Produit::actif()->with('categorie');

        if ($request->filled('recherche')) {
            $terme = '%' . $request->recherche . '%';
            $q->where(fn($r) => $r->where('nom','like',$terme)->orWhere('marque','like',$terme)->orWhere('description','like',$terme));
        }
        if ($request->filled('categorie')) {
            $q->whereHas('categorie', fn($r) => $r->where('slug', $request->categorie));
        }
        if ($request->filled('marque')) {
            $q->where('marque', $request->marque);
        }
        if ($request->filled('prix_min')) {
            $q->where('prix', '>=', $request->prix_min);
        }
        if ($request->filled('prix_max')) {
            $q->where('prix', '<=', $request->prix_max);
        }
        if ($request->boolean('en_stock')) {
            $q->where('stock', '>', 0);
        }
        if ($request->boolean('en_promo')) {
            $q->whereNotNull('prix_promo');
        }

        match($request->get('tri', 'recent')) {
            'prix_asc'  => $q->orderByRaw('COALESCE(prix_promo, prix) ASC'),
            'prix_desc' => $q->orderByRaw('COALESCE(prix_promo, prix) DESC'),
            'nom'       => $q->orderBy('nom'),
            default     => $q->latest(),
        };

        $produits = $q->paginate((int)$request->get('par_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $produits->items(),
            'meta'    => [
                'total'        => $produits->total(),
                'par_page'     => $produits->perPage(),
                'page_actuelle'=> $produits->currentPage(),
                'derniere_page'=> $produits->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $produit = Produit::actif()->with('categorie')->where('slug', $slug)->firstOrFail();
        $similaires = Produit::actif()->where('categorie_id', $produit->categorie_id)
            ->where('id', '!=', $produit->id)->limit(6)->get();

        return response()->json([
            'success' => true,
            'data'    => ['produit' => $produit, 'similaires' => $similaires],
        ]);
    }

    public function vedettes(): JsonResponse
    {
        $produits = Produit::vedette()->with('categorie')->limit(8)->get();
        return response()->json(['success' => true, 'data' => $produits]);
    }

    public function promotions(): JsonResponse
    {
        $produits = Produit::actif()->whereNotNull('prix_promo')->with('categorie')->limit(8)->get();
        return response()->json(['success' => true, 'data' => $produits]);
    }

    public function nouveautes(): JsonResponse
    {
        $produits = Produit::actif()->with('categorie')->latest()->limit(8)->get();
        return response()->json(['success' => true, 'data' => $produits]);
    }

    public function categories(): JsonResponse
    {
        $cats = Categorie::where('actif', true)->withCount(['produits as nb_produits' => fn($q) => $q->where('actif',true)])->get();
        return response()->json(['success' => true, 'data' => $cats]);
    }
}
