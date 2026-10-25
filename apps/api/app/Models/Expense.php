<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['batch_id', 'date', 'montant', 'categorie', 'description', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date', 'montant' => 'integer'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
