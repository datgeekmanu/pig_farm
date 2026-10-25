<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = [
        'date_vente', 'acheteur', 'acheteur_tel', 'acheteur_email',
        'mode_paiement', 'statut_paiement', 'total', 'notes',
    ];

    protected function casts(): array
    {
        return ['date_vente' => 'date', 'total' => 'integer'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
