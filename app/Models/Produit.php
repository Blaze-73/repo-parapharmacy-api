<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $table    = 'produits';
    protected $fillable = [
        'nom', 'slug', 'description', 'image', 'categorie_id',
        'marque', 'prix', 'prix_promo', 'stock', 'actif', 'en_vedette',
    ];
    protected $casts = [
        'prix'       => 'decimal:2',
        'prix_promo' => 'decimal:2',
        'actif'      => 'boolean',
        'en_vedette' => 'boolean',
    ];
    protected $appends = ['prix_effectif', 'remise', 'en_solde', 'en_stock'];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function getPrixEffectifAttribute(): float
    {
        return (float) ($this->prix_promo ?? $this->prix);
    }

    public function getRemiseAttribute(): ?int
    {
        if ($this->prix_promo && $this->prix > 0) {
            return (int) round((($this->prix - $this->prix_promo) / $this->prix) * 100);
        }
        return null;
    }

    public function getEnSoldeAttribute(): bool
    {
        return $this->prix_promo !== null;
    }

    public function getEnStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    public function scopeActif($q)
    {
        return $q->where('actif', true);
    }

    public function scopeVedette($q)
    {
        return $q->where('en_vedette', true)->where('actif', true);
    }
}
