<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintStation extends Model
{
    protected $fillable = ['name', 'is_default'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function stationSizes(): HasMany
    {
        return $this->hasMany(PrintStationSize::class);
    }

    public function rateForSize(Size $size): float
    {
        return (float) ($this->stationSizes->firstWhere('size_id', $size->id)?->rate ?? $size->rate);
    }
}
