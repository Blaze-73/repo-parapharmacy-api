<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'nom'      => 'Administrateur',
            'email'    => 'admin@parapharmacie.ma',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Client de test
        User::create([
            'nom'      => 'Client Test',
            'email'    => 'client@parapharmacie.ma',
            'password' => Hash::make('password'),
            'role'     => 'client',
        ]);

        // Categories
        $cats = [
            ['nom' => 'Soins du visage',  'slug' => 'soins-visage',   'icone' => '✨'],
            ['nom' => 'Vitamines',         'slug' => 'vitamines',       'icone' => '💊'],
            ['nom' => 'Bébé & Maman',      'slug' => 'bebe-maman',      'icone' => '👶'],
            ['nom' => 'Cheveux',           'slug' => 'cheveux',         'icone' => '💆'],
            ['nom' => 'Solaires',          'slug' => 'solaires',        'icone' => '☀️'],
            ['nom' => 'Hygiène',           'slug' => 'hygiene',         'icone' => '🧴'],
            ['nom' => 'Nutrition',         'slug' => 'nutrition',       'icone' => '🥗'],
            ['nom' => 'Premiers secours',  'slug' => 'premiers-secours','icone' => '🩹'],
        ];

        foreach ($cats as $c) {
            Categorie::create($c);
        }

        // Produits
        $produits = [
            ['nom'=>'Crème Hydratante Visage SPF20','description'=>'Crème hydratante légère avec protection solaire SPF20. Convient à tous les types de peau.','marque'=>'La Roche-Posay','prix'=>189,'prix_promo'=>null,'stock'=>45,'categorie_slug'=>'soins-visage','en_vedette'=>true],
            ['nom'=>'Sérum Acide Hyaluronique','description'=>'Sérum ultra-hydratant à l\'acide hyaluronique pour une peau rebondie et éclatante.','marque'=>'Vichy','prix'=>220,'prix_promo'=>185,'stock'=>32,'categorie_slug'=>'soins-visage','en_vedette'=>true],
            ['nom'=>'Eau Micellaire Sensitive 500ml','description'=>'Eau micellaire douce pour démaquiller et nettoyer la peau sensible sans rinçage.','marque'=>'Bioderma','prix'=>145,'prix_promo'=>null,'stock'=>67,'categorie_slug'=>'soins-visage','en_vedette'=>false],
            ['nom'=>'Crème Visage Hydratante 340g','description'=>'Crème hydratante riche pour les peaux sèches et très sèches. Sans parfum.','marque'=>'CeraVe','prix'=>175,'prix_promo'=>null,'stock'=>28,'categorie_slug'=>'soins-visage','en_vedette'=>true],
            ['nom'=>'Vitamine C 1000mg — 60 comprimés','description'=>'Complément alimentaire en vitamine C pour renforcer le système immunitaire.','marque'=>'Vitarmonyl','prix'=>89,'prix_promo'=>72,'stock'=>90,'categorie_slug'=>'vitamines','en_vedette'=>false],
            ['nom'=>'Oméga-3 Huile de Poisson — 60 capsules','description'=>'Acides gras essentiels pour le cœur, le cerveau et les articulations.','marque'=>'Pileje','prix'=>135,'prix_promo'=>null,'stock'=>55,'categorie_slug'=>'vitamines','en_vedette'=>true],
            ['nom'=>'Vitamine D3 2000 UI — 90 comprimés','description'=>'Vitamine D3 pour les os, les dents et le système immunitaire.','marque'=>'SVR','prix'=>98,'prix_promo'=>null,'stock'=>78,'categorie_slug'=>'vitamines','en_vedette'=>false],
            ['nom'=>'Huile Sèche Corps et Cheveux 100ml','description'=>'Huile sèche multi-usages pour sublimer la peau et les cheveux. Parfum fleuri.','marque'=>'Nuxe','prix'=>250,'prix_promo'=>null,'stock'=>23,'categorie_slug'=>'cheveux','en_vedette'=>true],
            ['nom'=>'Shampoing Doux Usage Fréquent','description'=>'Shampoing doux pour les cheveux fins et sensibles. Usage quotidien.','marque'=>'René Furterer','prix'=>79,'prix_promo'=>65,'stock'=>60,'categorie_slug'=>'cheveux','en_vedette'=>false],
            ['nom'=>'Crème Solaire SPF50+ Visage 50ml','description'=>'Très haute protection solaire pour le visage. Texture légère, sans résidu blanc.','marque'=>'La Roche-Posay','prix'=>215,'prix_promo'=>null,'stock'=>50,'categorie_slug'=>'solaires','en_vedette'=>true],
            ['nom'=>'Spray Nasal Bébé 60ml','description'=>'Solution isotonique pour dégager le nez des bébés dès la naissance.','marque'=>'Physiomer','prix'=>65,'prix_promo'=>null,'stock'=>35,'categorie_slug'=>'bebe-maman','en_vedette'=>false],
            ['nom'=>'Crème Change Bébé 75ml','description'=>'Crème protectrice pour prévenir et traiter les rougeurs du siège.','marque'=>'Mustela','prix'=>95,'prix_promo'=>80,'stock'=>42,'categorie_slug'=>'bebe-maman','en_vedette'=>false],
            ['nom'=>'Gel Douche Surgras Peaux Sèches','description'=>'Gel douche surgras enrichi en huile de karité pour les peaux sèches et sensibles.','marque'=>'Avène','prix'=>55,'prix_promo'=>null,'stock'=>80,'categorie_slug'=>'hygiene','en_vedette'=>false],
            ['nom'=>'Déodorant 48h Peaux Sensibles','description'=>'Déodorant sans alcool ni aluminium pour les peaux sensibles et après épilation.','marque'=>'Vichy','prix'=>75,'prix_promo'=>null,'stock'=>65,'categorie_slug'=>'hygiene','en_vedette'=>false],
            ['nom'=>'Protéines Whey Vanille 500g','description'=>'Protéines de lactosérum pour la récupération musculaire après le sport.','marque'=>'Nutrisens','prix'=>195,'prix_promo'=>170,'stock'=>30,'categorie_slug'=>'nutrition','en_vedette'=>false],
            ['nom'=>'Pansements Assortis Boîte 40','description'=>'Boîte de 40 pansements de différentes tailles pour toute la famille.','marque'=>'Urgo','prix'=>35,'prix_promo'=>null,'stock'=>100,'categorie_slug'=>'premiers-secours','en_vedette'=>false],
        ];

        foreach ($produits as $p) {
            $cat = Categorie::where('slug', $p['categorie_slug'])->first();
            Produit::create([
                'nom'         => $p['nom'],
                'slug'        => Str::slug($p['nom']),
                'description' => $p['description'],
                'marque'      => $p['marque'],
                'prix'        => $p['prix'],
                'prix_promo'  => $p['prix_promo'],
                'stock'       => $p['stock'],
                'categorie_id'=> $cat->id,
                'en_vedette'  => $p['en_vedette'],
                'actif'       => true,
                'image'       => null,
            ]);
        }

        $this->command->info('✅ Base de données remplie avec succès !');
        $this->command->info('   Admin  : admin@parapharmacie.ma / password');
        $this->command->info('   Client : client@parapharmacie.ma / password');
    }
}
