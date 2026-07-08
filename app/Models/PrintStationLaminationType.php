<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['print_station_id', 'lamination_type_id', 'rate'])]
class PrintStationLaminationType extends Model
{
    protected $table = 'print_station_lamination_type';

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
        ];
    }

    public function printStation(): BelongsTo
    {
        return $this->belongsTo(PrintStation::class);
    }

    public function laminationType(): BelongsTo
    {
        return $this->belongsTo(LaminationType::class);
    }
}
