<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['farm_id', 'user_id', 'auditable_type', 'auditable_id', 'action', 'changes', 'created_at'];

    protected function casts(): array
    {
        return ['changes' => 'array', 'created_at' => 'datetime'];
    }
}
