<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Commande extends Model
{
    protected $table    = 'commandes';
    protected $fillable = [
        'user_id', 'numero', 'statut', 'sous_total',
        'frais_livraison', 'total', 'paiement',
        'adresse_livraison', 'ville', 'code_postal', 'notes',
    ];
    protected $casts = [
        'sous_total'      => 'decimal:2',
        'frais_livraison' => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($c) {
            if (! $c->numero) {
                $c->numero = 'CMD-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CommandeItem::class, 'commande_id');
    }
}
