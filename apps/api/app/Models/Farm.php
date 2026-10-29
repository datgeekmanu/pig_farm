<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
  'account_id',
  'name',
  'currency',
  'seuil_rentabilite',
  'phone',
  'email',
  'address',
  'payment_info',
  'receipt_color',
  'logo_path',
  'notification_prefs',
])]
class Farm extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'seuil_rentabilite' => 'integer',
      'notification_prefs' => 'array',
    ];
  }

  public function account(): BelongsTo
  {
    return $this->belongsTo(Account::class);
  }

  public function users(): BelongsToMany
  {
    return $this->belongsToMany(User::class)
      ->withPivot('role')
      ->withTimestamps();
  }
}
