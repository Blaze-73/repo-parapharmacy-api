<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// ── Auth public ──────────────────────────────────────────────────────────────
Route::post('/inscription',  [AuthController::class, 'register']);
Route::post('/connexion',    [AuthController::class, 'login']);

// ── Produits public ───────────────────────────────────────────────────────────
Route::get('/produits',          [ProduitController::class, 'index']);
Route::get('/produits/vedettes', [ProduitController::class, 'vedettes']);
Route::get('/produits/promotions',[ProduitController::class, 'promotions']);
Route::get('/produits/nouveautes',[ProduitController::class, 'nouveautes']);
Route::get('/produits/{slug}',   [ProduitController::class, 'show']);
Route::get('/categories',        [ProduitController::class, 'categories']);

// ── Authentifié ───────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'logout']);
    Route::get('/moi',          [AuthController::class, 'me']);

    // Commandes
    Route::get('/commandes',           [CommandeController::class, 'index']);
    Route::get('/commandes/{id}',      [CommandeController::class, 'show']);
    Route::post('/commandes',          [CommandeController::class, 'store']);
    Route::post('/commandes/{id}/annuler', [CommandeController::class, 'annuler']);

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/tableau-de-bord',    [AdminController::class, 'dashboard']);
        Route::get('/produits',           [AdminController::class, 'produits']);
        Route::post('/produits',          [AdminController::class, 'storeProduit']);
        Route::put('/produits/{id}',      [AdminController::class, 'updateProduit']);
        Route::delete('/produits/{id}',   [AdminController::class, 'deleteProduit']);
        Route::get('/commandes',          [AdminController::class, 'commandes']);
        Route::put('/commandes/{id}/statut', [AdminController::class, 'updateStatutCommande']);
        Route::get('/utilisateurs',       [AdminController::class, 'utilisateurs']);
        Route::get('/categories',         [AdminController::class, 'categories']);
        Route::post('/categories',        [AdminController::class, 'storeCategorie']);
    });
});
