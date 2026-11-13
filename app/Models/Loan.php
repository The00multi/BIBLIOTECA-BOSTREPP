<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = ['reader_id', 'copy_id', 'borrowed_at', 'due_at', 'returned_at', 'status'];

    protected function casts(): array
    {
        return ['borrowed_at' => 'date', 'due_at' => 'date', 'returned_at' => 'date'];
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(Copy::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }
}
