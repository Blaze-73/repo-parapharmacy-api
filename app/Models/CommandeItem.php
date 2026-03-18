<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeItem extends Model
{
    protected $table    = 'commande_items';
    protected $fillable = [
        'commande_id', 'produit_id', 'nom_produit',
        'quantite', 'prix_unitaire', 'total',
    ];
    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }
}
