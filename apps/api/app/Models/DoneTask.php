<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoneTask extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['task_key', 'done_on'];

    protected function casts(): array
    {
        return ['done_on' => 'date'];
    }
}
