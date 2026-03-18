<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->string('icone')->nullable()->default('💊');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Products
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('categorie_id')->constrained('categories')->cascadeOnDelete();
            $table->string('marque')->nullable();
            $table->decimal('prix', 10, 2);
            $table->decimal('prix_promo', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('actif')->default(true);
            $table->boolean('en_vedette')->default(false);
            $table->timestamps();
        });

        // Orders
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->enum('statut', ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'])->default('en_attente');
            $table->decimal('sous_total', 10, 2);
            $table->decimal('frais_livraison', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('paiement', ['carte', 'livraison'])->default('livraison');
            $table->string('adresse_livraison');
            $table->string('ville');
            $table->string('code_postal')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Order items
        Schema::create('commande_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->string('nom_produit');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande_items');
        Schema::dropIfExists('commandes');
        Schema::dropIfExists('produits');
        Schema::dropIfExists('categories');
    }
};
