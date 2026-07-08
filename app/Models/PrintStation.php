<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintStation extends Model
{
    protected $fillable = ['name', 'is_default', 'requires_cutting'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'requires_cutting' => 'boolean',
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

    public function stationCuttingTypes(): HasMany
    {
        return $this->hasMany(PrintStationCuttingType::class);
    }

    public function stationLaminationTypes(): HasMany
    {
        return $this->hasMany(PrintStationLaminationType::class);
    }

    public function rateForSize(Size $size): float
    {
        return (float) ($this->stationSizes->firstWhere('size_id', $size->id)?->rate ?? $size->rate);
    }

    public function rateForCuttingType(?CuttingType $type): float
    {
        if (! $type) {
            return 0.0;
        }

        return (float) ($this->stationCuttingTypes->firstWhere('cutting_type_id', $type->id)?->rate ?? 0);
    }

    public function rateForLaminationType(?LaminationType $type): float
    {
        if (! $type) {
            return 0.0;
        }

        return (float) ($this->stationLaminationTypes->firstWhere('lamination_type_id', $type->id)?->rate ?? 0);
    }
}
