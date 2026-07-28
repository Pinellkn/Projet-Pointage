<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'check_in_date',
        'check_in_time',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_in_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
