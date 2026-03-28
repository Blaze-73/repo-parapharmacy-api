<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeItem;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $commandes = Commande::where('user_id', $request->user()->id)
            ->with('items')->latest()->get();
        return response()->json(['success' => true, 'data' => $commandes]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $commande = Commande::where('user_id', $request->user()->id)
            ->with('items.produit')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $commande]);
    }

    public function store(Request $request): JsonResponse
    {
         $user = $request->user();

    // Block if user placed more than 3 orders today
    $ordersToday = Commande::where('user_id', $user->id)
        ->whereDate('created_at', today())
        ->count();

    if ($ordersToday >= 3) {
        return response()->json([
            'success' => false,
            'message' => 'Vous avez atteint la limite de commandes pour aujourd\'hui.',
        ], 429);
    }

    // Block if any single item quantity exceeds 10
    foreach ($request->items as $item) {
        if ($item['quantite'] > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Quantité maximale de 10 par produit.',
            ], 422);
        }}
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.produit_id'=> 'required|exists:produits,id',
            'items.*.quantite'  => 'required|integer|min:1',
            'adresse_livraison' => 'required|string',
            'ville'             => 'required|string',
            'paiement'          => 'required|in:carte,livraison',
            'notes'             => 'nullable|string',
        ], [
            'items.required'             => 'Le panier est vide.',
            'adresse_livraison.required' => 'L\'adresse de livraison est obligatoire.',
            'ville.required'             => 'La ville est obligatoire.',
        ]);

        return DB::transaction(function () use ($request) {
            $sousTtotal = 0;
            $lignes = [];

            foreach ($request->items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                if ($produit->stock < $item['quantite']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuffisant pour : {$produit->nom}",
                    ], 422);
                }
                $prixUnit = $produit->prix_effectif;
                $lignes[] = [
                    'produit'      => $produit,
                    'quantite'     => $item['quantite'],
                    'prix_unitaire'=> $prixUnit,
                    'total'        => $prixUnit * $item['quantite'],
                ];
                $sousTtotal += $prixUnit * $item['quantite'];
            }

            $fraisLivraison = $sousTtotal >= 300 ? 0 : 30;
            $total          = $sousTtotal + $fraisLivraison;

            $commande = Commande::create([
                'user_id'           => $request->user()->id,
                'sous_total'        => $sousTtotal,
                'frais_livraison'   => $fraisLivraison,
                'total'             => $total,
                'paiement'          => $request->paiement,
                'adresse_livraison' => $request->adresse_livraison,
                'ville'             => $request->ville,
                'code_postal'       => $request->code_postal,
                'notes'             => $request->notes,
            ]);

            foreach ($lignes as $ligne) {
                CommandeItem::create([
                    'commande_id'   => $commande->id,
                    'produit_id'    => $ligne['produit']->id,
                    'nom_produit'   => $ligne['produit']->nom,
                    'quantite'      => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'total'         => $ligne['total'],
                ]);
                $ligne['produit']->decrement('stock', $ligne['quantite']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commande passée avec succès !',
                'data'    => $commande->load('items'),
            ], 201);
        });
    }

    public function annuler(Request $request, int $id): JsonResponse
    {
        $commande = Commande::where('user_id', $request->user()->id)->findOrFail($id);

        if (!in_array($commande->statut, ['en_attente', 'confirmee'])) {
            return response()->json(['success' => false, 'message' => 'Cette commande ne peut plus être annulée.'], 422);
        }

        $commande->update(['statut' => 'annulee']);

        // Restaurer le stock
        foreach ($commande->items as $item) {
            $item->produit?->increment('stock', $item->quantite);
        }

        return response()->json(['success' => true, 'message' => 'Commande annulée.']);
    }
}
